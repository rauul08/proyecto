from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
import time

# Configuración de ChromeDriver
chrome_options = Options()
chrome_options.add_argument("--headless")  # Para ejecutar en modo headless (sin abrir el navegador)
s = Service('C:\\Users\\delac\\AppData\\Local\\Microsoft\\WindowsApps\\chromedriver.exe') 
driver = webdriver.Chrome(service=s, options=chrome_options)

try:
    # Abre la página de añadir productos
    driver.get('http://localhost/proyecto/admin/phpAdmin/a%C3%B1adirProductos.php')

    # Espera un momento para asegurarte de que la página cargue completamente
    time.sleep(2)

    nombre_input = driver.find_element(By.ID, 'nombre') # Encuentra el campo "Nombre del Producto" y escribe un nombre
    nombre_input.send_keys('Producto de Prueba')

    descripcion_input = driver.find_element(By.ID, 'descripcion')  # Encuentra el campo "Descripción" y escribe una descripción
    descripcion_input.send_keys('Esta es una descripción para el producto de prueba.')

    precio_input = driver.find_element(By.ID, 'precio') # Encuentra el campo "Precio" y escribe un precio
    precio_input.send_keys('20')

    registrar_button = driver.find_element(By.CSS_SELECTOR, 'button[type="submit"]')  # Encuentra el botón de "Registrar" y haz clic en él
    registrar_button.click()

    # Espera un momento para que se procese la solicitud
    time.sleep(2)

    # Verifica si se muestra un mensaje de éxito
    mensaje = driver.find_element(By.CSS_SELECTOR, '.alert.alert-info')
    print(mensaje.text)  # Esto debería imprimir "Producto añadido exitosamente"

finally:
    # Cierra el navegador
    driver.quit()
