<?php

namespace Lagree;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\server\DataPacketReceiveEvent;
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {
    
    public const FORM_RESPONSE = 22222;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->notice("이용약관 플러그인 | 제작자: 라떼");
        
        @mkdir($this->getDataFolder());
        $this->data = new Config($this->getDataFolder() . "Lagree.yml", Config::YAML);
        $this->db = $this->data->getAll();
    }
    public function onSave(){
        $this->data->setAll($this->db);
        $this->data->save();
    }
    
    public function AgreeUI(Player $player) {
        $form = [
            "type" => "modal",
            "title" => "§6[카페 온라인 이용 약관]",
            "content" => "카페서버에 오신걸 환영합니다! [카페온라인 이용약관] 제 1조 서버는 법상 게임으로 분류한다. 제 2조 유저들은 서버의 정해진 규칙에 따라 행동해야 하며 이를 어길시에는 처벌이 내려질수있습니다. 제 3조 어드민은 유저들에게 공평히 게임을 즐길수있게 공급해줘야하며, 만약 후원금이나 여러 율라의 법들을 이행하지 않을시에는 사퇴당할수있다.",
            "button1" => "Y(동의)",
            "button2" => "N(비동의)",
            ];
        $pk = new ModalFormRequestPacket();
        $pk->formId = self::FORM_RESPONSE;
        $pk->formData = json_encode($form);
        $player->sendDataPacket($pk); 
     }
    public function onReceive(DataPacketReceiveEvent $event){
    $pk = $event->getPacket();
    $player = $event->getPlayer();
    $name = $player->getName();
    
  if ($pk instanceof ModalFormResponsePacket) {
            if ($pk->formId == self::FORM_RESPONSE) {
                $data = json_decode($pk->formData, true);
                if ($data === true) {
                    $this->db[$name]["동의"] = "Y";
       EconomyAPI::getInstance()->addMoney($player, 5000);
$player->sendMessage("서버의 이용약관에 동의하여주셔서 소정의 선물과 함께 즐거운 플레이 되십시오.");
$this-> onSave();
}
            }
if(is_null($data))
     {
$player->kick("약관을 동의 하지 않아 서버이용이 불가합니다");
      }
else if($data === false) {
$this->db[$name]["동의"] = "N";
$player->kick("약관을 동의 하지 않아 서버이용이 불가합니다");
$this->onSave();
}
}
}
            
public function onJoin(PlayerJoinEvent $event) {
    $player = $event->getPlayer();
$name = $player->getName();
if(!isset($this->db[$name])) {
              $this->AgreeUI($player);
                     }
  else if($this->db[$name]["동의"] == "N"){
$this->AgreeUI($player);  
  } else{
      $player->sendTitle("서버에 오신걸 환영합니다!");
  }
  return true;
}
    
}