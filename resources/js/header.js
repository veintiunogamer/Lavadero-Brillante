// Small header toggle for mobile menu
(function(){
    function isBrowser() {
        return typeof window !== 'undefined' && typeof document !== 'undefined';
    }
    if (!isBrowser()) return;

    document.addEventListener('DOMContentLoaded', function(){
        var toggle = document.getElementById('nav-toggle');
        var header = document.querySelector('.main-header');
        var nav = document.querySelector('.main-nav');

        if (!toggle || !header || !nav) return;

        toggle.addEventListener('click', function(e){
            header.classList.toggle('nav-open');
            var opened = header.classList.contains('nav-open');
            toggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
        });

        // Close menu when clicking outside (mobile)
        document.addEventListener('click', function(e){
            if (!header.classList.contains('nav-open')) return;
            if (header.contains(e.target)) return;
            header.classList.remove('nav-open');
            toggle.setAttribute('aria-expanded', 'false');
        });

        // User dropdown toggle
        var userToggle = document.getElementById('user-toggle');
        var userDropdown = document.querySelector('.user-dropdown');
        var userMenu = document.querySelector('.user-menu');

        if (userToggle && userDropdown && userMenu) {
            userToggle.addEventListener('click', function(e){
                e.stopPropagation();
                var opened = userDropdown.classList.toggle('open');
                userToggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
                userMenu.setAttribute('aria-hidden', opened ? 'false' : 'true');
            });

            // Close user menu on outside click
            document.addEventListener('click', function(e){
                if (!userDropdown.classList.contains('open')) return;
                if (userDropdown.contains(e.target)) return;
                userDropdown.classList.remove('open');
                userToggle.setAttribute('aria-expanded', 'false');
                userMenu.setAttribute('aria-hidden', 'true');
            });

            // Close on Escape
            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape' && userDropdown.classList.contains('open')) {
                    userDropdown.classList.remove('open');
                    userToggle.setAttribute('aria-expanded', 'false');
                    userMenu.setAttribute('aria-hidden', 'true');
                }
            });
        }
    });
})();
