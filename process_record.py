import os 
import sys
import json
import requests
from datetime import date
from supabase import create_client, Client
import logging
from collections import defaultdict
import re

# Configurar logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

# Cargar credenciales
try:
    SUPABASE_URL = os.environ.get("SUPABASE_URL")
    SUPABASE_KEY = os.environ.get("SUPABASE_KEY")
    
    if not SUPABASE_URL or not SUPABASE_KEY:
        logger.error("Faltan variables de entorno SUPABASE_URL o SUPABASE_KEY")
        sys.exit(1)
        
    supabase: Client = create_client(SUPABASE_URL, SUPABASE_KEY)
    logger.info("Conexión a Supabase configurada")
except Exception as e:
    logger.error(f"Error configurando Supabase: {str(e)}")
    sys.exit(1)

PDF_CO_API_KEY = os.environ.get("PDF_CO_API_KEY")
BASE_URL = "https://api.pdf.co/v1"

def upload_pdf_to_pdfco(pdf_path):
    try:
        filename = os.path.basename(pdf_path)
        url = f"{BASE_URL}/file/upload/get-presigned-url?contenttype=application/octet-stream&name={filename}"
        headers = {"x-api-key": PDF_CO_API_KEY}
        
        response = requests.get(url, headers=headers).json()
        if response.get("error"):
            raise Exception(f"Error al obtener presigned URL: {response['message']}")

        presigned_url = response["presignedUrl"]
        uploaded_url = response["url"]

        with open(pdf_path, 'rb') as f:
            upload_resp = requests.put(presigned_url, data=f, headers={
                "x-api-key": PDF_CO_API_KEY,
                "content-type": "application/octet-stream"
            })
            if upload_resp.status_code != 200:
                raise Exception(f"Error al subir PDF: {upload_resp.text}")

        logger.info(f"PDF subido exitosamente a: {uploaded_url}")
        return uploaded_url
    except Exception as e:
        logger.error(f"Error en upload_pdf_to_pdfco: {str(e)}")
        raise

def convert_pdf_to_json(pdf_url):
    try:
        convert_url = f"{BASE_URL}/pdf/convert/to/json"
        payload = {
            "url": pdf_url,
            "pages": "",
            "password": "",
            "name": "converted.json"
        }
        headers = {"x-api-key": PDF_CO_API_KEY}

        logger.info("Iniciando conversión PDF a JSON...")
        response = requests.post(convert_url, data=payload, headers=headers).json()

        if response.get("error", True):
            raise Exception(f"Error al convertir PDF: {response.get('message', 'desconocido')}")

        result_url = response["url"]
        logger.info(f"Descargando JSON desde: {result_url}")
        result_response = requests.get(result_url)
        result_response.raise_for_status()

        json_data = result_response.json()
        logger.info("Conversión a JSON completada exitosamente")

        # Transformar el JSON al formato deseado
        json_data = transform_json_format(json_data)

        # Guardar copia de depuración
        debug_path = os.path.join(os.path.dirname(__file__), "debug_pdf_output.json")
        with open(debug_path, 'w', encoding='utf-8') as f:
            json.dump(json_data, f, indent=2, ensure_ascii=False)
        logger.info(f"Datos de depuración guardados en: {debug_path}")
        #logger.info(f"Datos JSON obtenidos:\n{json.dumps(json_data, indent=2, ensure_ascii=False)}")
        return json_data
    except Exception as e:
        logger.error(f"Error en convert_pdf_to_json: {str(e)}")
        raise

def transform_json_format(original_json):
    transformed_json = []

    for page in original_json.get("document", {}).get("page", []):
        new_page = {
            "row": []
        }

        for row in page.get("row", []):
            new_row = {
                "column": []
            }

            for col in row.get("column", []):
                # Transformar cada celda
                text_data = col.get("text", {})
                if text_data:
                    transformed_text = {
                        "fontName": text_data.get("@fontName"),
                        "fontSize": text_data.get("@fontSize"),
                        "x": text_data.get("@x"),
                        "y": text_data.get("@y"),
                        "width": text_data.get("@width"),
                        "height": text_data.get("@height"),
                        "text": text_data.get("#text")  # Cambiar a "text"
                    }
                    new_row["column"].append({"text": transformed_text})
            new_page["row"].append(new_row)

        transformed_json.append(new_page)

    return {"document": {"page": transformed_json}}

def extract_all_approved_subjects(json_data):
    try:
        logger.info("Extrayendo todas las materias aprobadas del historial...")
        pages = json_data.get("document", {}).get("page", [])

        all_subjects = []
        current_period = None

        def extract_text(cell):
            if isinstance(cell, dict):
                return cell.get("text", "").strip()
            return str(cell).strip()

        for page in pages:
            for row in page.get("row", []):
                # Acceder directamente a las columnas
                texts = [extract_text(col.get("text", "")) for col in row.get("column", [])]
                full_text = " | ".join(filter(None, texts)).strip()
                if not full_text:
                    continue

                # Buscar el período actual
                period_match = re.search(r"PERIODO:\s*(\d{6})", full_text, re.IGNORECASE)
                if period_match:
                    current_period = period_match.group(1)
                    continue

                # Procesar filas de materias aprobadas
                if current_period and len(texts) >= 6:
                    try:
                        if texts[0].isdigit() and "APROBADO" in texts[-1].upper():
                            code = texts[1].strip()

                            # Reconstruir nombre de la materia (puede estar entre columnas 2 hasta antepenúltima)
                            name_parts = texts[2:-3]
                            subject_name = " ".join(name_parts).strip()

                            credits_raw = texts[-3].strip()
                            grade_raw = texts[-2].strip()
                            status = texts[-1].strip()

                            credits = int(credits_raw) if credits_raw.isdigit() else 0
                            grade = float(grade_raw) if re.match(r"^\d+\.?\d*$", grade_raw) else 0.0

                            all_subjects.append({
                                "code": code,
                                "name": subject_name,
                                "credits": credits,
                                "grade": grade,
                                "status": status,
                                "period_code": current_period
                            })

                    except (ValueError, IndexError) as e:
                        logger.warning(f"Error procesando fila: {full_text} - {str(e)}")

        if not all_subjects:
            logger.warning("No se encontraron materias aprobadas en el documento")
            return []

        logger.info(f"Retornando {len(all_subjects)} materias aprobadas del historial completo")
        return all_subjects

    except Exception as e:
        logger.error(f"Error en extract_all_approved_subjects: {str(e)}")
        raise

def insert_subjects(subjects, student_id):
    try:
        if not subjects:
            logger.warning("No hay materias para insertar")
            return

        logger.info(f"Iniciando inserción para estudiante {student_id}")
        
        # Eliminar TODAS las materias anteriores del estudiante (no solo del último período)
        logger.info(f"Eliminando TODAS las materias anteriores del estudiante {student_id}")
        supabase.table("approved_subjects").delete().eq("student_id", student_id).execute()

        # Agrupar materias por período para optimizar las consultas
        periods = {}
        for subject in subjects:
            period_code = subject['period_code']
            if period_code not in periods:
                periods[period_code] = []
            periods[period_code].append(subject)

        # Procesar cada período
        for period_code, period_subjects in periods.items():
            # Buscar o crear período
            period_query = supabase.table("registration_periods").select("period_id").eq("code", period_code).execute()
            
            if period_query.data:
                period_id = period_query.data[0]['period_id']
                logger.info(f"Período existente encontrado: {period_id}")
            else:
                logger.info(f"Creando nuevo período: {period_code}")
                new_period = supabase.table("registration_periods").insert({
                    "code": period_code,
                    "start_date": str(date.today()),
                    "end_date": str(date.today()),
                    "admin_id": 3
                }).execute()
                period_id = new_period.data[0]['period_id']
                logger.info(f"Período creado con ID: {period_id}")

            # Procesar materias del período
            for subject in period_subjects:
                #logger.info(f"Procesando materia: {subject['code']} - {subject['name']}")
                
                # Buscar materia existente
                subject_query = supabase.table("subjects").select("subject_id").eq("code", subject["code"]).execute()
                
                if subject_query.data:
                    subject_id = subject_query.data[0]['subject_id']
                    logger.info(f"Materia existente encontrada: {subject_id}")
                else:
                    #logger.info("Creando nueva materia...")
                    new_subj = supabase.table("subjects").insert({
                        "code": subject["code"],
                        "name": subject["name"],
                        "credits": subject["credits"],
                        "level": 1  # Nivel por defecto, podría mejorarse
                    }).execute()
                    subject_id = new_subj.data[0]['subject_id']
                    logger.info(f"Materia creada con ID: {subject_id}")

                # Insertar relación aprobada
                insert_result = supabase.table("approved_subjects").insert({
                    "registration_date": str(date.today()),
                    "student_id": student_id,
                    "subject_id": subject_id,
                    "period_id": period_id
                }).execute()
                
                logger.info(f"Materia {subject['code']} asociada al estudiante para el período {period_code}")

        logger.info("Proceso de inserción completado exitosamente")

    except Exception as e:
        logger.error(f"Error en insert_subjects: {str(e)}")
        raise


if __name__ == "__main__":
    try:
        if len(sys.argv) != 3:
            logger.error("Uso: python process_record.py <pdf_path> <student_id>")
            sys.exit(1)

        pdf_path = sys.argv[1]
        student_id = int(sys.argv[2])
        
        logger.info(f"Procesando PDF: {pdf_path} para estudiante ID: {student_id}")

        # Paso 1: Subir PDF a PDF.co
        uploaded_url = upload_pdf_to_pdfco(pdf_path)
        
        # Paso 2: Convertir a JSON
        json_data = convert_pdf_to_json(uploaded_url)
        
        # Paso 3: Extraer TODAS las materias aprobadas
        subjects = extract_all_approved_subjects(json_data)
        logger.info(f"Materias aprobadas extraídas:\n{json.dumps(subjects, indent=2, ensure_ascii=False)}")
        
        # Paso 4: Insertar en Supabase
        insert_subjects(subjects, student_id)
        
        logger.info("Proceso completado exitosamente")
        sys.exit(0)

    except Exception as e:
        logger.error(f"Error en el proceso principal: {str(e)}")
        sys.exit(1)