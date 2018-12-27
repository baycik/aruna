

ROBOCOPY "../admin\language\ru-ru" "./RussianLanguagePack.ocmod/upload/admin\language\ru-ru"
ROBOCOPY "../catalog\language\ru-ru" "./RussianLanguagePack.ocmod/upload/catalog\language\ru-ru"


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "./RussianLanguagePack.ocmod.zip" "./*"
PAUSE