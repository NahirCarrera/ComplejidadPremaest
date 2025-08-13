// scripts/cifrarUsuarios.js
import { supabase } from '../lib/supabaseClient.js';
import bcrypt from 'bcryptjs';

async function run() {
  const { data: users, error } = await supabase
    .from('users')
    .select('user_id, username, password');

  if (error) {
    console.error('❌ Error obteniendo usuarios:', error.message);
    return;
  }

  let updatedCount = 0;

  for (const user of users) {
    const pwd = user.password || '';

    // Verificar si la contraseña ya parece estar cifrada con bcrypt
    const isHashed = pwd.startsWith('$2a$') || pwd.startsWith('$2b$') || pwd.startsWith('$2y$');

    if (!isHashed) {
      try {
        const hash = await bcrypt.hash(pwd, 10);

        const { error: updateError } = await supabase
          .from('users')
          .update({ password: hash })
          .eq('user_id', user.user_id);

        if (updateError) {
          console.error(`❌ Error actualizando usuario ${user.username}:`, updateError.message);
        } else {
          console.log(`✅ Contraseña cifrada para ${user.username}`);
          updatedCount++;
        }
      } catch (err) {
        console.error(`❌ Error al cifrar la contraseña de ${user.username}:`, err);
      }
    } else {
      console.log(`🔒 Contraseña ya cifrada para ${user.username}, se omite.`);
    }
  }

  console.log(`\n🔁 Total de contraseñas actualizadas: ${updatedCount}`);
}

run();
