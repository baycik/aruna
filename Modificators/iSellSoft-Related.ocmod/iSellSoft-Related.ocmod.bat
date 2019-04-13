ROBOCOPY "../../catalog/language/en-gb/extension/module/" "./upload/catalog/language/en-gb/extension/module/"  iss_related.php
ROBOCOPY "../../catalog/language/ru-ru/extension/module/" "./upload/catalog/language/ru-ru/extension/module/"  iss_related.php
ROBOCOPY "../../catalog/controller/extension/module/" "./upload/catalog/controller/extension/module/"  iss_related.php
ROBOCOPY "../../catalog/model/extension/module/" "./upload/catalog/model/extension/module/"  iss_related.php
ROBOCOPY "../../catalog/view/theme/default/template/extension/module/" "./upload/catalog/view/theme/default/template/extension/module/"  iss_related.twig

ROBOCOPY "../../admin/language/en-gb/extension/module/" "./upload/admin/language/en-gb/extension/module/"  iss_related.php
ROBOCOPY "../../admin/language/ru-ru/extension/module/" "./upload/admin/language/ru-ru/extension/module/"  iss_related.php
ROBOCOPY "../../admin/view/template/extension/module/" "./upload/admin/view/template/extension/module/"  iss_related.twig
ROBOCOPY "../../admin/controller/extension/module/" "./upload/admin/controller/extension/module/"  iss_related.php


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "../../../iSellSoft-Related1.0.ocmod.zip" "./*"
PAUSE