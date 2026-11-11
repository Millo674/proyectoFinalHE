<?php

// --- Mitigación: control de intentos fallidos ---
if( !isset( $_SESSION[ 'login_attempts' ] ) ) {
        $_SESSION[ 'login_attempts' ] = 0;
        $_SESSION[ 'lockout_time' ]   = 0;
}

$max_intentos   = 5;
$tiempo_bloqueo = 30; // segundos

$bloqueado = ( $_SESSION[ 'login_attempts' ] >= $max_intentos &&
               ( time() - $_SESSION[ 'lockout_time' ] ) < $tiempo_bloqueo );

if( $bloqueado ) {
        $restante = $tiempo_bloqueo - ( time() - $_SESSION[ 'lockout_time' ] );
        $html .= "<pre><br />Demasiados intentos fallidos. Intenta de nuevo en {$restante} segundos.</pre>";
}
elseif( isset( $_GET[ 'Login' ] ) ) {

        // Solo reinicia si realmente hubo un bloqueo previo y ya expiró
        if( $_SESSION[ 'lockout_time' ] > 0 &&
            ( time() - $_SESSION[ 'lockout_time' ] ) >= $tiempo_bloqueo ) {
                $_SESSION[ 'login_attempts' ] = 0;
                $_SESSION[ 'lockout_time' ]   = 0;
        }

        // Get username
        $user = $_GET[ 'username' ];

        // Get password
        $pass = $_GET[ 'password' ];
        $pass = md5( $pass );

        // Check the database
        $query  = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
        $result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

        if( $result && mysqli_num_rows( $result ) == 1 ) {
                $row    = mysqli_fetch_assoc( $result );
                $avatar = $row["avatar"];

                $_SESSION[ 'login_attempts' ] = 0;

                $html .= "<p>Welcome to the password protected area {$user}</p>";
                $html .= "<img src=\"{$avatar}\" />";
        }
        else {
                $_SESSION[ 'login_attempts' ]++;
                sleep( $_SESSION[ 'login_attempts' ] );

                if( $_SESSION[ 'login_attempts' ] >= $max_intentos ) {
                        $_SESSION[ 'lockout_time' ] = time();
                }

                $html .= "<pre><br />Username and/or password incorrect.</pre>";
        }

        ((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>
