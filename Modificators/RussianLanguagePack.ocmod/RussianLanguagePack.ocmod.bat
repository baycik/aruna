

ROBOCOPY "../../admin\language\ru-ru" "./upload/admin\language\ru-ru"
ROBOCOPY "../../catalog\language\ru-ru" "./upload/catalog\language\ru-ru"


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "../../../RussianLanguagePack.ocmod.zip" "./*"
PAUSE