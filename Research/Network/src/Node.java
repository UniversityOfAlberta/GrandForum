import java.util.Vector;

public class Node {

	private String name;
	private String type;
	private Vector<Edge> edges;
	private Vector<Edge> sourceEdges;
	private Vector<Edge> targetEdges;
	
	/**
	 * Constructs a new Node
	 * @param name The name of the Node
	 * @param type The type of Node
	 */
	public Node(String name, String type){
		this.name = name;
		this.type = type;
		this.edges = new Vector<Edge>();
		this.sourceEdges = new Vector<Edge>();
		this.targetEdges = new Vector<Edge>();
	}
	
	/**
	 * Adds an edge to this Node
	 * @param e The Edge to add
	 */
	public void addEdge(Edge e){
		this.edges.add(e);
		if(e.getSource() == this){
			this.sourceEdges.add(e);
		}
		else{
			this.targetEdges.add(e);
		}
	}
	
	/**
	 * Returns the name of this Node
	 * @return the name of this Node
	 */
	public String getName(){
		return this.name;
	}
	
	/**
	 * Returns the type of this Node
	 * @return the type of this Node
	 */
	public String getType(){
		return this.type;
	}
	
	/**
	 * Returns the edges connected to this Node
	 * @return the edges connected to this Node
	 */
	public Vector<Edge> getEdges(){
		return this.edges;
	}
	
	/**
	 * Returns the edges where this Node is the source
	 * @return the edges where this Node is the source
	 */
	public Vector<Edge> getSourceEdges(){
		return this.sourceEdges;
	}
	
	/**
	 * Returns the edges where this Node is the target
	 * @return the edges where this Node is the target
	 */
	public Vector<Edge> getTargetEdges(){
		return this.targetEdges;
	}
	
	/**
	 * Returns the degree of this Node (number of 1st degree edges)
	 * @return the degree of this Node (number of 1st degree edges)
	 */
	public double degree(){
		double degree = 0;
		for(Edge e : this.edges){
			degree += e.getCount();
		}
		return degree;
	}
	
	public String toString(){
		return this.name;
	}
	
}
