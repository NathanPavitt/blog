 package  {

	public class Action {
	
		public var position:Number;
		public var frame:Number;
		public var action_to_execute:Object;
	
		public function Action( pos:Number, fme:Number, act:Object ):void {
			this.position = pos;
			this.frame = fme;
			this.action_to_execute = act;
		}
	}
}

