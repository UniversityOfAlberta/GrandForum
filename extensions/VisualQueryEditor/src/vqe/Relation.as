package vqe
{
	public class Relation
	{
		private var name:String;
		private var type:String;
		private var actor1:String;
		private var actor2:String;
		private var varType:String;
		private var varOperator:String;
		private var varValue:String;
		private var steps:int;
		
		public function Relation(name:String, type:String, actor1:String, actor2:String)
		{
			this.name = name;
			this.type = type;
			this.actor1 = actor1;
			this.actor2 = actor2;
		}
		
		public function addVariable(varType:String, varOperator:String, varValue:String):void {
			this.varType = varType;
			this.varOperator = varOperator;
			this.varValue = varValue;
		}
		
		public function setSteps(s:int):void {
			this.steps = s;
		}
		
		public function getName():String {
			return name;
		}
		
		public function getType():String {
			return type;
		}
		
		public function getActor1():String {
			return actor1;
		}
		
		public function getActor2():String {
			return actor2;
		}
		
		public function getVarType():String {
			return varType;
		}
		
		public function getVarOperator():String {
			return varOperator;
		}
		
		public function getvarValue():String {
			return varValue;
		}
		
		public function getSteps():int {
			return steps;
		}
	}
}