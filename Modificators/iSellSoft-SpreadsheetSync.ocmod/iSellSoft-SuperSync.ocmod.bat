

ROBOCOPY "../../admin/controller/extension/module/" "./upload/admin/controller/extension/module/" "iss_superparserlist.php"  /mir
ROBOCOPY "../../admin/controller/extension/module/" "./upload/admin/controller/extension/module/" "iss_supersync.php"  /mir
ROBOCOPY "../../admin/controller/extension/module/" "./upload/admin/controller/extension/module/" "iss_supersyncCron.php"  /mir

ROBOCOPY "../../admin\language\en-gb\extension\module" "./upload/admin\language\en-gb\extension\module" "iss_superparserlist.php" /mir
ROBOCOPY "../../admin\language\en-gb\extension\module" "./upload/admin\language\en-gb\extension\module" "iss_supersync.php" /mir
ROBOCOPY "../../admin\language\en-gb\extension\module" "./upload/admin\language\en-gb\extension\module" "iss_supersyncCron.php" /mir

ROBOCOPY "../../admin\language\tr-tr\extension\module" "./upload/admin\language\tr-tr\extension\module" "iss_superparserlist.php" /mir
ROBOCOPY "../../admin\language\tr-tr\extension\module" "./upload/admin\language\tr-tr\extension\module" "iss_supersync.php" /mir
ROBOCOPY "../../admin\language\tr-tr\extension\module" "./upload/admin\language\tr-tr\extension\module" "iss_supersyncCron.php" /mir

ROBOCOPY "../../admin/model/extension/module/iss_supersync/" "./upload/admin/model/extension/module/iss_supersync/"  /mir

ROBOCOPY "../../admin/view/template/extension/module/" "./upload/admin/view/template/extension/module/" iss_superparserlist.twig 
ROBOCOPY "../../admin/view/template/extension/module/" "./upload/admin/view/template/extension/module/" iss_supersync.twig 


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "../../../iSellSoft-SuperSync.ocmod.zip" "./*"
PAUSE
