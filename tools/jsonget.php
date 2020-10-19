<?php
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
    $.get( "json.php", { address: "11111111111111111111111111111"} )
        .done(function( data ) {
            alert( "Data Loaded: " + data );
        });
</script>
