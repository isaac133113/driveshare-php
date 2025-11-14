<nav class="navbar navbar-expand-lg navbar-light" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border-bottom: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../../public/index.php?controller=dashboard&action=index" style="color: #6366f1;">
            <i class="bi bi-map-fill"></i> DriveShare
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link fw-semibold" href="../../public/index.php?controller=dashboard&action=index" style="color: #1f2937; padding: 0.5rem 1rem; margin: 0 0.25rem; border-radius: 0.75rem; transition: all 0.2s;">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link fw-semibold" href="../../public/index.php?controller=rutes&action=index" style="color: #1f2937; padding: 0.5rem 1rem; margin: 0 0.25rem; border-radius: 0.75rem; transition: all 0.2s;">
                    <i class="bi bi-map"></i> Veure rutes
                </a>
                <a class="nav-link fw-semibold" href="../../public/index.php?controller=horaris&action=index" style="color: #1f2937; padding: 0.5rem 1rem; margin: 0 0.25rem; border-radius: 0.75rem; transition: all 0.2s;">
                    <i class="bi bi-calendar"></i> Horaris
                </a>
                <a class="nav-link fw-semibold" href="../../public/index.php?controller=valoracion&action=index" style="color: #1f2937; padding: 0.5rem 1rem; margin: 0 0.25rem; border-radius: 0.75rem; transition: all 0.2s;">
                    <i class="bi bi-star"></i> Valoracions
                </a>
                <a class="nav-link fw-semibold" href="../../public/index.php?controller=map&action=index" style="color: #1f2937; padding: 0.5rem 1rem; margin: 0 0.25rem; border-radius: 0.75rem; transition: all 0.2s;">
                    <i class="bi bi-geo-alt"></i> Mapa
                </a>
                <a class="nav-link fw-semibold" href="../../public/index.php?controller=auth&action=profile" style="color: #1f2937; padding: 0.5rem 1rem; margin: 0 0.25rem; border-radius: 0.75rem; transition: all 0.2s;">
                    <i class="bi bi-person"></i> Perfil
                </a>
                <a class="nav-link fw-semibold" href="../../public/index.php?controller=auth&action=logout" style="color: #1f2937; padding: 0.5rem 1rem; margin: 0 0.25rem; border-radius: 0.75rem; transition: all 0.2s;">
                    <i class="bi bi-box-arrow-right"></i> Sortir
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
.navbar .nav-link:hover {
    color: #6366f1 !important;
    background: rgba(99, 102, 241, 0.1);
    transform: translateY(-2px);
}
</style>