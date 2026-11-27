(function(){
    const btn = document.getElementById('btnSidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');

    function isMobile(){
        return window.matchMedia('(max-width: 991.98px)').matches;
    }

    function toggleSidebar(){
        if (isMobile()){
            document.body.classList.toggle('sidebar-open');
        }else{
            document.body.classList.toggle('sidebar-collapsed');
        }
    }

    function closeMobileSidebar(){
        document.body.classList.remove('sidebar-open');
    }

    btn?.addEventListener('click', toggleSidebar);
    backdrop?.addEventListener('click', closeMobileSidebar);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMobileSidebar();
    });

    // khi đổi kích thước màn hình, reset class cho sạch
    window.addEventListener('resize', () => {
        document.body.classList.remove('sidebar-open');
    });
})();