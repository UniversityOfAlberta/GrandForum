package vqe
{
	public class Property
	{
		private var id: int;
		private var name: String;
		private var type: String;
		private var actorName: String;
		
		public function Property(id:int, name:String, type:String, actorName:String)
		{
			this.id = id;
			this.name = name;
			this.type = type;
			this.actorName = actorName;
		}
		
		public function getId(): int 
		{
			return id;
		}
		
		public function getName(): String
		{
			return name;
		}
		
		public function getType(): String
		{
			return type;
		}
		
		public function getActorName(): String
		{
			return actorName;
		}
	}
}