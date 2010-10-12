 package  {


	import flash.display.*;
	import flash.events.*;
	import flash.utils.Timer;

	public class Show {

		private var frame_counter:Number = 0;
		private var actions:Object = new Object();
		private var frame_listener:Object = null;
		private var action_counter:Number = 0;
		private var highest_frame:Number = 0;
		private var main_timer:Timer; 

		private const FRAME_LABEL:String  = "frame_";

		public function Show():void {
            this.main_timer = new Timer(1000, 0);
            this.main_timer.addEventListener("timer", do_it);
        }

        private function do_it(event:TimerEvent):void {
			
			var frame_label:String = FRAME_LABEL + this.frame_counter; 
			
			if(this.actions[frame_label] != null){
				this.actions[frame_label]();
			}
			
			if (this.frame_listener != null){
				this.frame_listener();
			}
			
			if (this.frame_counter == this.highest_frame){
				this.frame_counter = 0;
			}
			else{
				this.frame_counter++;
			}
			
        }

		public function start():void{
            this.main_timer.start();
		}

		public function add_action (frame:Number, action:Object, duration:Number = 1 ):void{
			var last_frame:Number = (frame + duration); 
			if (this.highest_frame < last_frame){
				this.highest_frame = last_frame;
			}

			var act:Action = new Action(actions.length, frame, action);
			this.actions[FRAME_LABEL + frame] = action;
		}
		
		public function set_frame_listener(action:Object):void{
			this.frame_listener = action;
		}
		
		public function get_frame():Number{
			return this.frame_counter;
		}
	}
}

