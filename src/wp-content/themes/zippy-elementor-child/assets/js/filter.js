$(function(){
    $('.dropdown-btn').on('click', function(){
        const parentDropdown = $(this).closest('.custom-dropdown');
        const dropdownArea = parentDropdown.find('.dropdown-area');
        if (dropdownArea.hasClass('show')) {
            dropdownArea.slideUp();
            dropdownArea.removeClass('show');
        } else {
            dropdownArea.slideDown();
            dropdownArea.addClass('show');
        }
    })
})