<header>
        <nav class="fondo">
            <ul>
                <li class="busqueda">
                
                <form action="index.php" method="GET" autocomplete="off">
                <input type="text" name="search" id="seeker"
                placeholder=" calidad garantizada en todos tus pedidos" value="<?php echo htmlspecialchars($search); ?>">
                <!--<button class="invincible" type="submit"><i class="fas fa-search"></i></button>-->
                </form>

            <a class="nav-link" href="checkout.php">| Pedidos<span id="num_cart" class="badge bg-secondary"><?php echo $total_items;?></span>. |</a>
            <?php if(isset($_SESSION['user_id'])) { ?>
            <div class="dropdown">
                <button class="dropdown-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 20px; height: 20px;">
                        <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/>
                    </svg>
                    <?php echo $_SESSION['user_name']; ?>
                </button>
                <div class="dropdown-content">
                    <a href="logout.php">Cerrar sesión</a>
                </div>
            </div>

            <?php } else { ?>
                <a class="nav-link" href="login.php"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
                Ingresar</a>
                <?php } ?>
                </li>
            </ul>
        </nav>  
</header>