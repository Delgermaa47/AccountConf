
<script src="http://localhost:81/assets/js/jquery-3.5.1.min.js"></script>
<script src="http://localhost:81/assets/js/bootstrap.bundle.min.js"></script>
<script>
    function getPage(url) {
        $.get(url,
        function(data, status, jqXHR) {// success callback
            var main_div = document.getElementById('content')
            main_div.innerHTML = data;
        })
    }

</script>
</body>
</html>