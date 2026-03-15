<?php 

function esNulo(array $parametros)
{
    foreach($parametros as $parametro) {
        if(strlen(trim($parametro)) < 1) {
        return true;
        }
    }
    return false;
}

function mostrarMensajes(array $errors) {
    if(count($errors) > 0) {
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert"><ul>';
        foreach($errors as $error) {
            echo '<li>'. $error .'</li>'; 
        }
        echo '<ul>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}
