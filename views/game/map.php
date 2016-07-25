<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Progress;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\web\View;
use app\models\Land;
use app\controllers\AjaxController;


/* @var $this yii\web\View */
$this->title = Yii::t('game', 'Title_Map');
$refresh_time = $this->context->refreshTime;

// Set JS var
$this->registerJs($this->context->getJSConfig(), View::POS_HEAD);

// Call files
$this->registerJsFile("@web/js/game/map.js", ['depends' => [dmstr\web\AdminLteAsset::className()]]);
$this->registerJsFile("@web/js/game/game.js", ['depends' => [dmstr\web\AdminLteAsset::className()]]);
$this->registerJsFile("@web/js/game/ajax.js", ['depends' => [dmstr\web\AdminLteAsset::className()]]);
$this->registerCssFile("@web/css/map.css");
?>

<div class="map-show">
	<!-- Modals Js -->
    <?php
        Modal::begin(['id' => 'modal-view','header' => '<div class="modal-header-title"></div>']);
        echo "<div id='modal-view-Content'></div>";
        Modal::end();
    ?>
    <!-- End Modal Js -->
	<?php Pjax::begin(['id' => 'map_content']); ?>
	<div id='map_content'>
		<?php $user_units = 0; ?>
		
		<?php foreach ($GameData as $data): ?>
			
			<?php $land = $Land[$data->getGameDataLandId()]; ?>  
			<div class="land_content" i=<?= "'".$land->getLandId()."'"; ?>>
				  <!-- Image -->
	              <a href=<?= "'#".str_replace("'", "-", $land->getLandName())."'"; ?> class="link_land_img" style=<?= "'top:".$land->getLandPositionTop()."em;left:".$land->getLandPositionLeft()."em;text-decoration: none;'"; ?>>
	                    <img src=<?= "'".$land->getLandImageTempUrl($Color[$GamePlayer[$data->getGameDataUserId()]->getGamePlayerColorId()]->getColorName2())."'"; ?> i=<?= "'".$land->getLandId()."'"; ?> alt=<?= '"'.$land->getLandName().'"'; ?> class="land_img" 
	                    style=<?= "'top:".$land->getLandPositionTop()."em;left:".$land->getLandPositionLeft()."em;'"; ?>> 
	                    <!--<div class="building" style=<?= "'position:absolute;top:".$land->getLandPositionTop()."em;left:".$land->getLandPositionLeft()."em;'"; ?>>
	                        <?php //if($value['land_harbor']): ?>
	                            <img src='img/harbor.png' height='20px' width='20px' style='position:relative;top:24px;left:10px;'>
	                        <?php //endif; ?>
	                    </div>-->
	                </a>
	               <!-- Title -->
	               <div class="land_title" style=<?= "'top:".$land->getLandPositionTop()."em;left:".$land->getLandPositionLeft()."em;'"; ?>>
                        <font color=<?= "'".$Color[$GamePlayer[$data->getGameDataUserId()]->getGamePlayerColorId()]->getColorName()."'"; ?>>
                         	<!-- <?= $land->getLandName(); ?> -->
                         	<?= $land->getLandName(); ?>
                         	<?php if(\app\models\Frontier::userHaveFrontierLand($UserFrontier, $land->getLandId())): ?>
	                         	<!-- Land data -->   
	                            <?php if($data->getGameDataCapital() >= 1): ?>
	                                <?= "<img src='img/star.png' height='20px' width='20px'>"; ?>
	                            <?php endif; ?>
	                            <?php if($data->getGameDataRessourceId() > 0 && $Ressource[$data->getGameDataRessourceId()]->getRessourceImage() != ""): ?>
	                                <?= "<img src='".$Ressource[$data->getGameDataRessourceId()]->getRessourceImageUrl()."' height='20px' width='20px'>"; ?>
	                            <?php endif; ?>
	                            <!-- Buildings -->
	                            <?php foreach($GameData[$land->getLandId()-1]->getGameDataBuildings() as $building): ?>
									<?php if($building != null && $Building[$building]->getBuildingId() > 0): ?>
										<i class="<?= $Building[$building]->getBuildingImg() ?>"></i>
						            <?php endif; ?>
						        <?php endforeach; ?>
                        		<!-- Units -->
		                     	<?php $land_units = Land::LandCountUnitsToArray($data->getGameDataUnits());?>
		                     	<?php for($i=1; $i <= $land_units['canon']; $i++): ?>
		                       		<img src='img/canon.png' class='land_canon' style=<?= "'left:".$i."px;'"; ?>>
		                   		<?php endfor; ?>
		                    	<?php for($i=1; $i <= $land_units['horseman']; $i++): ?>
		                        	<img src='img/horseman.png' class='land_horseman' style=<?= "'left:".$i."px;'"; ?>>
		                    	<?php endfor; ?>
		                    	<?php for($i=1; $i <= $land_units['soldier']; $i++): ?>
		                    		<img src='img/soldier.png' class='land_soldier' style=<?= "'left:".$i."px;'"; ?>>
		                  		<?php endfor; ?>
	                  		<?php endif; ?>
	                  	</font>	
                  </div>
	        </div>
		<?php endforeach; ?>
	
	</div>
	<?php Pjax::end(); ?>
</div>
						