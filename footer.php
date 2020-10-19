<?php
if ((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)) {
    echo "<br><br><br><br><br><br><br><br>";
    echo '<div style="position:fixed; bottom:0; width: 100%; background-color: #f0f0f0"><br>Contact us:<a href="mailto:admin@'.SITE_URL.'"><img border="0" alt="admin@'.SITE_URL.'" src="resources/img/email.png" width="50" height="50"></a><div><h6>Icons made by <a href="https://www.flaticon.com/authors/freepik" title="Freepik">Freepik</a> from <a href="https://www.flaticon.com/"         title="Flaticon">www.flaticon.com</a></h6></div>
</div>';
}
echo '<script>if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }</script></body></html>';