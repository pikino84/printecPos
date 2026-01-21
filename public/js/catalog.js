let page = 1;
let loading = false;

function loadMoreProducts() {
    if (loading) return;

    loading = true;
    $('#loader').show();

    const category = $('#categoryFilter').val();
    const categoryType = $('#categoryFilter option:selected').data('type') || 'printec';
    const search = $('#searchInput').val();
    const city = $('#cityFilter').val();

    page++;
    $.get(`?page=${page}&category=${encodeURIComponent(category)}&category_type=${encodeURIComponent(categoryType)}&search=${encodeURIComponent(search)}&city_id=${encodeURIComponent(city)}`, function (data) {
        if (data.trim().length > 0) {
            $('#productGrid').append(data);
        } else {
            $(window).off('scroll');
        }
        $('#loader').hide();
        loading = false;
    });
}

$(window).scroll(function () {
    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 400) {
        loadMoreProducts();
    }
});

$('#categoryFilter, #searchInput, #cityFilter').on('change keyup', function () {
    page = 1;
    const category = $('#categoryFilter').val();
    const categoryType = $('#categoryFilter option:selected').data('type') || 'printec';
    const search = $('#searchInput').val();
    const city = $('#cityFilter').val();

    $.get(`?page=1&category=${encodeURIComponent(category)}&category_type=${encodeURIComponent(categoryType)}&search=${encodeURIComponent(search)}&city_id=${encodeURIComponent(city)}`, function (data) {
        if ($.trim(data).length === 0) {
            $('#productGrid').html(`
                <div class="col-12 text-center mt-4">
                    <div class="alert alert-warning">
                        No se encontraron productos con los filtros aplicados.<br>
                        Intenta quitar alguno o buscar otra cosa.
                    </div>
                </div>
            `);
            $(window).off('scroll');
        } else {
            $('#productGrid').html(data);

            // Reactivar scroll
            $(window).off('scroll').on('scroll', function () {
                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 400) {
                    loadMoreProducts();
                }
            });
        }
    });
});
