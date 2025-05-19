 <?php
    // Questo file serve solo a verificare se Apache/PHP può eseguire script in questa directory.
    echo "Test file executed successfully!";

    // Aggiungiamo un log per confermare l'esecuzione anche nei log di Apache/PHP
    error_log("DEBUG_PRESENTYOU_TEST: test.php file executed from local/presentyou/", E_USER_NOTICE);
?>
    