<script src="http://localhost:81/assets/js/jquery-3.5.1.min.js"></script>
<script src="http://localhost:81/assets/js/bootstrap.bundle.min.js"></script>
<script>

    function loader(container_id) {
        var loader_content = `
            <div class="gp-loader text-center" id=${container_id + "_loader"}>
                <div>
                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                    <br/>
                        Түр хүлээнэ үү...
                </div>
            </div>
        `
        return loader_content
    }
    
    function getPage(url, container_id) {
        var main_div = document.getElementById(container_id)
        main_div.innerHTML = loader(container_id)
        $.get(url,
        function(data, status, jqXHR) {
            main_div.innerHTML = data;
        })
    }

    // function openModal(action_name, title, body) {
    //     var main_div =
    //     main_div.innerHTML = data;
    // }
</script>
</body>
</html>