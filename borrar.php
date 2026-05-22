# 1. Posicionarte en la carpeta donde tienes los archivos
cd ~/Downloads/"files (1)"   # ajusta si está en otro lado

# 2. Definir destino (cámbialo si la ruta real es otra)
DEST="/Applications/MAMP/htdocs/pms/app"
mkdir -p "$DEST/Controllers/Onboarding"
mkdir -p "$DEST/Services/Onboarding"
mkdir -p "$DEST/Views/onboarding/import"
mkdir -p "$DEST/Views/onboarding/steps"
mv ImportController.php              "$DEST/Controllers/Onboarding/"
mv WebScraperService.php             "$DEST/Services/Onboarding/"
mv HotelExtractorService.php         "$DEST/Services/Onboarding/"
mv HotelImportApplierService.php     "$DEST/Services/Onboarding/"
mv extract_form.php                  "$DEST/Views/onboarding/import/"
mv review.php                        "$DEST/Views/onboarding/import/"
mv imported.php                      "$DEST/Views/onboarding/import/"
mv step_import.php                   "$DEST/Views/onboarding/steps/"
echo "── Estructura final ──"
ls -la "$DEST/Controllers/Onboarding/"
ls -la "$DEST/Services/Onboarding/"
ls -la "$DEST/Views/onboarding/import/"
ls -la "$DEST/Views/onboarding/steps/"

