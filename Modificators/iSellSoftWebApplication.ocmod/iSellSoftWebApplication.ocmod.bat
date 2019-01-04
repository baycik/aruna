ROBOCOPY "../../catalog/controller/extension/module/" "./upload/catalog/controller/extension/module/" isellsoft_webapplication.php
ROBOCOPY "../../admin/controller/extension/module/" "./upload/admin/controller/extension/module/" iss_webapp.php

ROBOCOPY "../../admin/language/en-gb/extension/module/" "./upload/admin/language/en-gb/extension/module/" iss_webapp.php
ROBOCOPY "../../admin/language/ru-ru/extension/module/" "./upload/admin/language/ru-ru/extension/module/" iss_webapp.php

ROBOCOPY "../../admin/view/template/extension/module/" "./upload/admin/view/template/extension/module/" iss_webapp.twig


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "../../../iSellSoftWebApplication.ocmod.zip" "./*"
PAUSE