document.addEventListener("DOMContentLoaded", function () {
    let page = 2;
    let loading = false;
    let hasMore = true;

    const grid = document.getElementById("productGrid");
    const loader = document.getElementById("loader");
    const categoryFilter = document.getElementById("categoryFilter");
    const searchInput = document.getElementById("searchInput");

    function fetchProducts() {
        if (loading || !hasMore) return;

        loading = true;
        loader.style.display = "block";

        const category = categoryFilter.value;
        const search = searchInput.value;

        fetch(`/catalogo/fetch?page=${page}&category=${category}&search=${search}`)
            .then(res => res.json())
            .then(data => {
                grid.insertAdjacentHTML("beforeend", data.html);
                hasMore = data.hasMore;
                page++;
            })
            .finally(() => {
                loading = false;
                loader.style.display = "none";
            });
    }

    // Scroll infinito
    window.addEventListener("scroll", () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 300) {
            fetchProducts();
        }
    });

    // Filtros
    [categoryFilter, searchInput].forEach(input => {
        input.addEventListener("input", () => {
            page = 1;
            hasMore = true;
            grid.innerHTML = "";
            fetch(`/catalogo/fetch?page=1&category=${categoryFilter.value}&search=${searchInput.value}`)
                .then(res => res.json())
                .then(data => {
                    grid.innerHTML = data.html;
                    hasMore = data.hasMore;
                    page = 2;
                });
        });
    });
});
