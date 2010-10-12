 package  {

	//to use tween ing libs you need this -compiler.source-path PATH
	//height:113px;
	//width:930px;

	import Test;
	import Action;
	import Show;
	
	import caurina.transitions.*;
	import flash.display.*;
	import flash.events.*;
	import flash.net.URLRequest;
	import flash.net.navigateToURL;
	import flash.text.TextField;
    	import flash.text.TextFieldAutoSize;
    	import flash.text.TextFormat;
	import flash.utils.Timer;
  
  	import flash.display.Sprite;
	import flash.events.MouseEvent;
  
  
	public class Animation extends Sprite {
		 
	private var the_show:Show = new Show();

        private var debug_format:TextFormat = new TextFormat();
        private var format:TextFormat = new TextFormat();
        private var label_counter:TextField;
        private var label_debug:TextField;
		
	private var lena_img:MovieClip;
	private var powder_blue_img:MovieClip;
        private var label:TextField;
        private var right_label:TextField;

	private var url_to_click:String = "http://blog.atosorigin.com/2010/07/engaging-with-generation-y-as-future-loyal-business/";

	function Animation():void {

            stage.scaleMode = StageScaleMode.NO_SCALE;
            stage.align = StageAlign.TOP_LEFT;

            format.font = "Verdana";
            format.color = 0xFFFFFF;
            format.size = 26;
            format.bold = true;
            
            debug_format.font = "Verdana";
            debug_format.color = 0xFFFFFF;
            debug_format.size = 10;
            
		//display leana
		var loader:Loader = new Loader();
		loader.load(new URLRequest("/wp-content/themes/atosorigin/animation/angle_of_the_midlands.jpg"));
		lena_img = new MovieClip();
		lena_img.addChild(loader);
		lena_img.visible = false;
		addChild(lena_img);
		the_show.add_action(1, show_lena);  

		//display powder_blue
		var powder_blue_loader:Loader = new Loader();
		powder_blue_loader.load(new URLRequest("/wp-content/themes/atosorigin/animation/powder_blue.jpg"));
		powder_blue_img = new MovieClip();
		powder_blue_img.addChild(powder_blue_loader);
		powder_blue_img.visible = false;
		addChild(powder_blue_img);
		the_show.add_action(7, show_powder_blue); 

		//Display meet our team
		label = new TextField();
		label.autoSize = TextFieldAutoSize.LEFT;
		label.x = 28;
		label.y = 65;
		label.visible = false;
		label.defaultTextFormat = format;
		addChild(label);
		the_show.add_action(3, show_meet_our_team);

		//display right_label
		right_label = new TextField();
		right_label.autoSize = TextFieldAutoSize.LEFT;
		right_label.x = 450;
		right_label.y = 65;
		right_label.alpha = 0;
		right_label.visible = true;
		right_label.defaultTextFormat = format;
		right_label.blendMode = BlendMode.LAYER;
		addChild(right_label);
		the_show.add_action(5, show_right_label);


		//end the animation
		the_show.add_action(15, end_it_all);

		//set up the debug
		label_counter = new TextField();
            	label_counter.autoSize = TextFieldAutoSize.LEFT;
		label_counter.x = 5;
		label_counter.y = 5;
            	label_counter.defaultTextFormat = debug_format;
            	addChild(label_counter);

            	label_debug = new TextField();
            	label_debug.autoSize = TextFieldAutoSize.LEFT;
		label_debug.x = 60;
		label_debug.y = 5;
            	label_debug.defaultTextFormat = debug_format;
            	addChild(label_debug);



			var bttn:Sprite = new Sprite();
			bttn.buttonMode = true;
			bttn.x = 0;
			bttn.x = 0;
			bttn.graphics.beginFill(0xFFCC00, 0.0);
			bttn.graphics.drawRect(0, 0, 930, 113);
			
			bttn.addEventListener(MouseEvent.CLICK, clicked);
			
			function clicked(event:MouseEvent):void {
				navigateToURL(new URLRequest(url_to_click));
			}
			
			addChild(bttn);
			
			//setup the frame counter
			the_show.set_frame_listener(frame_listener);
			
			//get the ball rolling
			the_show.start(); 
        }


		private function end_it_all():void{
			Tweener.removeAllTweens(); 

			powder_blue_img.visible = false;
			lena_img.visible = false;
			label.visible = false;
			right_label.alpha = 0;
		}

		private function debug(msg:String):void{
			//this.label_debug.text = msg;
		}

		private function show_meet_our_team():void{
            label.text = "Engaging with Generation Y";
			label.visible = true;
		}

		private function show_right_label():void{
			right_label.alpha = 0;
			right_label.text = "by Stephen Boyce";
			Tweener.addTween(right_label, {alpha:1, time:10, transition:"easeOutCubic"});
		}

		private function show_lena():void{
			debug(" show_lena frame=" + the_show.get_frame() + " lena_img.alpha=" + lena_img.alpha);
			lena_img.x = 0;
			lena_img.y = -50;
			lena_img.alpha = 1;
			lena_img.visible = true;
			Tweener.addTween(lena_img, {y:-400, alpha:0, time:15, transition:"easeOutCubic"});
		}
		
		private function show_powder_blue():void{
			powder_blue_img.x = 0;
			powder_blue_img.y = -90;
			powder_blue_img.alpha = 0;
			powder_blue_img.visible = true;
			Tweener.addTween(powder_blue_img, {x:-94, alpha:1, time:8, transition:"easeOutCubic"});
		}
		
		private function frame_listener():void{
			//label_counter.text = "frame_" + the_show.get_frame();			
			//debug("lena_img.alpha=" + lena_img.alpha);
		}

	}
}
