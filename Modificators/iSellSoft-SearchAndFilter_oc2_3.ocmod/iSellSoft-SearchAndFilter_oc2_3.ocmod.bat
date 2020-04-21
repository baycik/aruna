ROBOCOPY "../../catalog/language/en-gb/extension/module/" "./upload/catalog/language/en-gb/extension/module/"  iss_filter.php
ROBOCOPY "../../catalog/language/ru-ru/extension/module/" "./upload/catalog/language/ru-ru/extension/module/"  iss_filter.php
ROBOCOPY "../../catalog/controller/extension/module/" "./upload/catalog/controller/extension/module/"  iss_filter.php
ROBOCOPY "../../catalog/model/extension/module/" "./upload/catalog/model/extension/module/"  iss_filter.php
ROBOCOPY "../../catalog/view/theme/default/template/extension/module/" "./upload/catalog/view/theme/default/template/extension/module/"  iss_filter.tpl
ROBOCOPY "../../catalog/view/javascript/nouislider/" "./upload/catalog/view/javascript/nouislider/"  /mir

ROBOCOPY "../../admin/language/en-gb/extension/module/" "./upload/admin/language/en-gb/extension/module/"  iss_filter.php
ROBOCOPY "../../admin/language/ru-ru/extension/module/" "./upload/admin/language/ru-ru/extension/module/"  iss_filter.php
ROBOCOPY "../../admin/view/template/extension/module/" "./upload/admin/view/template/extension/module/"  iss_filter.tpl
ROBOCOPY "../../admin/controller/extension/module/" "./upload/admin/controller/extension/module/"  iss_filter.php


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "../../../iSellSoft-SearchAndFilter-1.3_oc2.3.x.ocmod.zip" "./*"
PAUSE