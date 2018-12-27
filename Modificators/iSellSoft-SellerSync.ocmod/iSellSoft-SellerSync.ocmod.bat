

ROBOCOPY "../../catalog/controller/extension/aruna/" "./upload/catalog/controller/extension/aruna/" /purge
ROBOCOPY "../../catalog/model/extension/aruna/" "./upload/catalog/model/extension/aruna/" /purge
ROBOCOPY "../../catalog/view/theme/default/template/extension/aruna/" "./upload/catalog/view/theme/default/template/extension/aruna/" /purge


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y -x*.bat -x*.zip "./iSellSoft-SellerSync.ocmod.zip" "./*"
PAUSE