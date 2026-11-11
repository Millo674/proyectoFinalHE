
<?php

if (isset($_POST['Upload'])) {

    // Directorio de almacenamiento
    $target_dir = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";

    // Comprobar que el archivo fue recibido correctamente
    if (
        !isset($_FILES['uploaded']) ||
        $_FILES['uploaded']['error'] !== UPLOAD_ERR_OK
    ) {
        $html .= '<pre>Error al recibir el archivo.</pre>';
    } else {

        $file = $_FILES['uploaded'];

        // Comprobar el tamaño máximo: 5 MB
        if ($file['size'] > 5 * 1024 * 1024) {
            $html .= '<pre>El archivo supera el tamaño permitido.</pre>';
        } else {

            // Extensiones permitidas y sus MIME correspondientes
            $allowed_types = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png'
            ];

            // Obtener extensión
            $extension = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            // Validar extensión
            if (!array_key_exists($extension, $allowed_types)) {
                $html .= '<pre>Tipo de archivo no permitido.</pre>';
            } else {

                // Detectar MIME real del archivo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                // Comprobar que sea una imagen válida
                $image_info = getimagesize($file['tmp_name']);

                if (
                    $mime !== $allowed_types[$extension] ||
                    $image_info === false ||
                    $image_info['mime'] !== $allowed_types[$extension]
                ) {
                    $html .= '<pre>El contenido del archivo no es válido.</pre>';
                } else {

                    // Generar un nombre aleatorio
                    $new_name = bin2hex(random_bytes(16)) . '.' . $extension;

                    // Construir la ruta final
                    $target_path = $target_dir . $new_name;

                    // Mover el archivo validado
                    if (
                        is_uploaded_file($file['tmp_name']) &&
                        move_uploaded_file(
                            $file['tmp_name'],
                            $target_path
                        )
                    ) {
                        $html .= '<pre>Imagen subida correctamente.</pre>';
                    } else {
                        $html .= '<pre>No se pudo guardar el archivo.</pre>';
                    }
                }
            }
        }
    }
}

?>

