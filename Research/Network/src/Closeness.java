import edu.uci.ics.jung.algorithms.scoring.ClosenessCentrality;

import edu.uci.ics.jung.graph.SparseMultigraph;

public class Closeness extends Centrality {

	private static double max = Double.MIN_VALUE;
	private static double min = Double.MAX_VALUE;
	
	private ClosenessCentrality<Node, Edge> closeness;
	
	public Closeness(SparseMultigraph<Node, Edge> graph){
		super(graph);
		this.closeness = new ClosenessCentrality<Node, Edge>(this.graph, new DisconnectedDistance(this.graph));
	}
	
	public void run(){
		double max = Double.MIN_VALUE;
		double min = Double.MAX_VALUE;
		
		// Calculating Closeness
		for(Node v : this.graph.getVertices()){
			double score = Math.pow(this.closeness.getVertexScore(v), Math.E);
			this.results.put(v.toString(), score);
			max = Math.max(max, score);
			min = Math.min(min, score);
		}
		//if(Closeness.min == Double.MAX_VALUE){
			Closeness.min = min;
			Closeness.max = max;
		//}
		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - Closeness.min)/(Closeness.max - Closeness.min);
			this.results.put(v.toString(), score);
		}
	}
	
}
