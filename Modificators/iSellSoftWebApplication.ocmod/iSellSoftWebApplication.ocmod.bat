

ROBOCOPY "../../catalog/controller/extension/module/isellsoft_webapplication.php" "./upload/catalog/controller/extension/module/isellsoft_webapplication.php"  /mir


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "../../../iSellSoftWebApplication.ocmod.zip" "./*"
cmd /k