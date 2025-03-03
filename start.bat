@echo off
rem Vérifie si PHP est installé
php --version >nul 2>&1
if errorlevel 1 (
    echo PHP n'est pas installé ou n'est pas configuré dans PATH.
    pause
    exit /b
)

rem Lancer le serveur PHP sur localhost:8080
echo Démarrage du serveur PHP sur localhost:8080...
php -S localhost:8000

rem Gérer la fermeture
echo Appuyez sur une touche pour fermer.
pause
