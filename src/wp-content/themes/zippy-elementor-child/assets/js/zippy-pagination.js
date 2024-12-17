$(function(){
    $(document).on('click', '.zippy-page-link', function(e) {
        e.preventDefault();

        var ajaxUrl = $('#ajax_url').val();
        var pagination = $(this).closest('.zippy-pagination');
        var category = pagination.data('category');
        var limit = pagination.data('limit');
        var paged = $(this).data('page');
        var productsContainer = pagination.siblings('.zippy-products-container');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'zippy_products_pagination',
                category: category,
                limit: limit,
                paged: paged
            },
            success: function(response) {
                if (response.success) {
                    productsContainer.html(response.data.products);
                    pagination.replaceWith(response.data.pagination);
                    $('html, body').animate({
                        scrollTop: productsContainer.offset().top - 200
                    }, 500);
                } else {
                    alert('Failed to load products.');
                }
            }
        });
    });
})
