<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIREH | Contacto</title>
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="09f">
    <link rel="stylesheet" href="../css/contacto.css">
    <link rel="icon" type="images/jpeg"  href="../images/jLeft.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fugaz+One&display=swap" rel="stylesheet">
    <meta property="og:title" content="Tienda de comida rapida">
    <meta property="og:description" content="Tienda en linea de la empresa de comida rapida JIREH">
</head>
<body>
    <div id="formulario">
        <div class="content-form">
            <div class="content-return">
            <h1 class="title-form">
            <a href="../html/conócenos.html" class="return">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="50"  height="50"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  
            class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" />
            </svg></a>Contacto <span>JIREH</span></h1>
            </div>

            <div class="contact-wrapper">
                <div class="contact-form">
                    <h2>Contacta con nosotros</h2>
                    <form action="../../admin/clases/buzonClientes.php" method="POST" autocomplete="off">
                        <p>
                            <label for="nombre">Nombre completo</label>
                            <input type="text" name="nombre" id="nombre" required>
                        </p>
                        <p>
                            <label for="email">E-mail</label>
                            <input type="email" name="email" id="email"required>
                        </p>
                        <p>
                            <label for="usuario">Usuario</label>
                            <input type="text" name="usuario" id="usuario">
                        </p>
                        <p>
                            <label for="asunto">Asunto</label>
                            <input type="text" name="asunto" id="asunto">
                        </p>
                        <p class="block">
                            <label>Comentario</label>
                            <textarea name="comentario" rows="3" required></textarea>
                        </p>
                        <p class="block">
                            <button type="submit" value="Enviar">
                                Enviar
                            </button>
                        </p>
                    </form>

                </div>
                <div class="contact-info">
                    <h3>Mas información</h3>
                    <ul>
                        <li><i class="fa fa-phone fa-rotate-90"></i><span> </span>+55 99 32 44 21 16</li>
                        <li><i class="fa fa-envelope" aria-hidden="true"></i><span> </span>jiireh_bussines@gmail.com</li>
                    </ul>
                    <p>En caso de alguna queja o comentario acerca del servicio ofrecido, favor de comunicarse al numero o al correo proporcionado. 
                        Aceptamos devoluciones con comprobante de transferencia o pago con tarjeta a través de paypal o tarjeta de debito.
                    </p>
                    <p class="end">Portal Web <br>
                        <span>JIREH</span> | Foods </p>
                </div>
            </div>
        
        </div>

    </div>
</body>
<footer>
    <span class="footer-line">©2024 JIREH Y ASOCIADOS, INC. ALGUNOS PRODUCTOS ESTAN SUJETOS A DISPONIBILIDAD<span>
</footer>
</html>