import java.util.Vector;


public class Node {

	private String name;
	private String type;
	private Vector<Edge> edges;
	private Vector<Edge> sourceEdges;
	private Vector<Edge> targetEdges;
	
	public Node(String name, String type){
		this.name = name;
		this.type = type;
		this.edges = new Vector<Edge>();
		this.sourceEdges = new Vector<Edge>();
		this.targetEdges = new Vector<Edge>();
	}
	
	public void addEdge(Edge e){
		this.edges.add(e);
		if(e.getSource() == this){
			this.sourceEdges.add(e);
		}
		else{
			this.targetEdges.add(e);
		}
	}
	
	public String getType(){
		return this.type;
	}
	
	public Vector<Edge> getEdges(){
		return this.edges;
	}
	
	public Vector<Edge> getSourceEdges(){
		return this.sourceEdges;
	}
	
	public Vector<Edge> getTargetEdges(){
		return this.targetEdges;
	}
	
	public int degree(){
		return this.edges.size();
	}
	
	public String toString(){
		return this.name;
	}
	
}
