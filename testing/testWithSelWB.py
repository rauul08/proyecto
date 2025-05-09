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

# URL de la página de inicio de sesión
driver.get('http://localhost/proyecto/clientes/phpClientes/login.php')  

# Esperar a que cargue la página
time.sleep(2)

# Buscar los campos de usuario y contraseña, e ingresar datos
usuario_input = driver.find_element(By.ID, 'usuario')
password_input = driver.find_element(By.ID, 'password')

usuario_input.send_keys('franx')  # Cambia por un usuario válido o de prueba
password_input.send_keys('12345678')  # Cambia por la contraseña válida o de prueba

# Enviar el formulario de inicio de sesión
password_input.send_keys(Keys.RETURN)

# Esperar a que el servidor procese la solicitud
time.sleep(3)

# Verificar si el inicio de sesión fue exitoso buscando algún elemento que solo aparece después de iniciar sesión
try:
    element = driver.find_element(By.CSS_SELECTOR, ".dropdown-btn")
    print("Inicio de sesión exitoso")
except:
    print("Error en el inicio de sesión")

# Cerrar el navegador
driver.quit()
