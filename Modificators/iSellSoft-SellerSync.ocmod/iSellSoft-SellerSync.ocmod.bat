

ROBOCOPY "../../catalog/controller/extension/aruna/" "./upload/catalog/controller/extension/aruna/"  /mir
ROBOCOPY "../../catalog/model/extension/aruna/" "./upload/catalog/model/extension/aruna/"  /mir
ROBOCOPY "../../catalog/view/theme/default/template/extension/aruna/" "./upload/catalog/view/theme/default/template/extension/aruna/"  /mir


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "../../../iSellSoft-SellerSync.ocmod.zip" "./*"
PAUSE