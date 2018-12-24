

ROBOCOPY "../catalog/controller/extension/aruna/" "./iSellSoft-SellerSync.ocmod/upload/catalog/controller/extension/aruna/"
ROBOCOPY "../catalog/model/extension/aruna/" "./iSellSoft-SellerSync.ocmod/upload/catalog/model/extension/aruna/"
ROBOCOPY "../catalog/view/theme/default/template/extension/aruna/" "./iSellSoft-SellerSync.ocmod/upload/catalog/view/theme/default/template/extension/aruna/"


"%ProgramFiles%\WinRAR\WinRAR.exe" a -afzip -ep1 -ibck -r -y "./iSellSoft-SellerSync.ocmod/iSellSoft-SellerSync.ocmod.zip" "./iSellSoft-SellerSync.ocmod/"

PAUSE