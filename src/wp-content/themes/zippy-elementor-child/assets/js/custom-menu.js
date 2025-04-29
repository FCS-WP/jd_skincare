$(function(){
    $('.redirect-item').on('click', function() {
        const redirect_url = $(this).data('link');
        console.log("redirect_url",redirect_url);   
        window.location.href = redirect_url;     
    })

    const menuItems = $('.custom-nav .elementor-nav-menu > li.menu-item-has-children');
    menuItems.on('click', function (e) {
        e.stopPropagation();

        $(this).toggleClass('active');
        menuItems.not(this).removeClass('active');
    });

    $('.custom-li-item.has-submenu').on('click', function() {
        const item_id = $(this).data('id');
        const childElement = $(`.child-of-${item_id}`);
        childElement.siblings('.child-box').hide();
        $('.child-box.child-2').hide();
        childElement.show();
    })
        document.addEventListener("DOMContentLoaded", function() {
        window.addEventListener('load', function() {
            const loaderWrapper = document.getElementById('loader-wrapper');
            if (loaderWrapper) {
                loaderWrapper.style.opacity = '0';
                setTimeout(() => {
                    loaderWrapper.style.display = 'none';
                }, 500);
            }
        });
    });
})