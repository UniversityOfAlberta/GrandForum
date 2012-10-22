package vqe
{
	public class Actor
	{
		private var id: int;
		private var name: String;
		private var alias: String;
		private static var count: int = 0;
		
		public function Actor(id:int, name:String)
		{
			this.id = id;
			this.name = name;
			Actor.count++;
			this.alias = "a"+count;
		}
		
		public function getId():int {
			return id;
		}
				
		public function getName():String {
			return name;
		}
				
		public function getAlias():String {
			return alias;
		}
		
		
		public function setName(name:String):void {
			this.name = name;
		}
		
		
		public function setAlias(alias:String):void {
			this.alias = alias;
		}
	}
}