 package  {

   import flash.display.Sprite;

   public class Test extends Sprite {

       function Test():void {


       }

	public function xxx(ting:Animation):void{

         var circle:Sprite = new Sprite();
         circle.graphics.beginFill(0xA307F2);
         circle.graphics.drawCircle(0, 0, 30);
         circle.graphics.endFill();
         ting.addChild(circle);

	}
    }
 }

