<!-- creates svg and png pictures (using create_png.php) and redirects to it when it is ready -->
<script src="https://code.jquery.com/jquery-1.11.1.js"></script>
<script src="http://crypto-js.googlecode.com/svn/tags/3.0.2/build/rollups/md5.js"></script>
<script>
$(document).ready(function () {
    postdata = {'url':CryptoJS.MD5(window.location.href).toString(), 'svg':$('#chart').html().replace(/<strong>/g,'').replace(/<\/strong>/g,'').replace(/<br>/g,'')};
    $.post('create_png.php',postdata);
    nothing = 0;
    get_picture();
});
var i = 0;
function get_picture() {
    $.ajax('cache/{_FORMAT}/' + CryptoJS.MD5(window.location.href).toString() + '.{_FORMAT}', {
        statusCode: {
          200: function (response) {
             location.href = 'cache/{_FORMAT}/' + CryptoJS.MD5(window.location.href).toString() + '.{_FORMAT}';
          },
          404: function(response) {
            i++;
            if (i < 60) {
              setTimeout(get_picture, 1000)
            } else {
                alert('Something wrong, giving up...');
            }
          }
        }
    });
}
</script>
