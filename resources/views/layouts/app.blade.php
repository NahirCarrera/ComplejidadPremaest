<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PREMAEST - @yield('title')</title>

    <!-- Custom fonts -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom styles -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-dark.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Items Dinámicos por Rol -->
            @auth
                @if(auth()->user()->hasRole('admin'))
                <!-- Sidebar - Brand -->
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('student.dashboard') }}">
                    <div class="sidebar-brand-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="sidebar-brand-text mx-3">PREMAEST ADMINISTRADOR</div>
                </a>
                    <!-- Menú para Admin -->
                    <li class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-fw fa-tachometer-alt"></i>
                            <span>Inicio</span>
                        </a>
                    </li>
                    
                    <li class="nav-item {{ request()->is('admin/periods*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.periods.index') }}">
                            <i class="fas fa-fw fa-calendar-alt"></i>
                            <span>Períodos Académicos</span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->is('admin/subjects*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.subjects.demand') }}">
                            <i class="fas fa-fw fa-book"></i>
                            <span>Demanda</span>
                        </a>
                    </li>


                @elseif(auth()->user()->hasRole('student'))
                <!-- Sidebar - Brand -->
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ auth()->user()->hasRole('admin') ? route('admin.dashboard') : route('student.dashboard') }}">
                    <div class="sidebar-brand-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="sidebar-brand-text mx-3">PREMAEST ESTUDIANTE</div>
                </a>
                    <!-- Menú para Estudiante -->
                    <li class="nav-item {{ request()->is('student/dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('student.dashboard') }}">
                            <i class="fas fa-fw fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="nav-item {{ request()->is('student/records/upload') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('student.records.upload') }}">
                            <i class="fas fa-fw fa-file-upload"></i>
                            <span>Registro Record</span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->is('student/records/approved') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('student.records.approved') }}">
                            <i class="fas fa-fw fa-file"></i>
                            <span>Record</span>
                        </a>
                    </li>
                    
                    <li class="nav-item {{ request()->is('student/pre-enrollment*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('student.pre-enrollment.plan') }}">
                            <i class="fas fa-fw fa-calendar-check"></i>
                            <span>Prematrícula</span>
                        </a>
                    </li>
                @endif
            @endauth

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ms-auto">
                        @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="me-2 d-none d-lg-inline text-gray-600">{{ auth()->user()->name }}</span>
                                <img class="img-profile rounded-circle" src="https://cdn-icons-png.flaticon.com/512/6073/6073873.png" width="32" height="32">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Cerrar sesión
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                        @endauth
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Main Content -->
                <div class="container-fluid py-4">
                    @include('partials.alerts')
                    @yield('content')
                </div>
            </div>
            <!-- End of Main Content -->


            <!-- Footer -->
            <footer class="sticky-footer">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; DVX PREMAEST ESPE {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="{{asset('jquery/jquery.min.js')}}"></script>
    <script src="{{asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{asset('jquery-easing/jquery.easing.min.js')}}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{asset('js/sb-admin-2.min.js')}}"></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom scripts -->
    <script>
        // Inicializar dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            // Activar todos los dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
            
            // Cerrar dropdown al hacer click fuera
            document.addEventListener('click', function(event) {
                if (!event.target.matches('.dropdown-toggle') && !event.target.closest('.dropdown-menu')) {
                    var dropdowns = document.querySelectorAll('.dropdown-menu');
                    dropdowns.forEach(function(dropdown) {
                        dropdown.classList.remove('show');
                    });
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>