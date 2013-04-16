import edu.uci.ics.jung.algorithms.scoring.BetweennessCentrality;

import edu.uci.ics.jung.graph.SparseMultigraph;

public class Betweenness extends Centrality {

	private static double max = Double.MIN_VALUE;
	private static double max2 = Double.MIN_VALUE;
	private static double min = Double.MAX_VALUE;
	private static double min2 = Double.MAX_VALUE;
	
	private BetweennessCentrality<Node, Edge> between;
	
	public Betweenness(SparseMultigraph<Node, Edge> graph){
		super(graph);
		EdgeWeightTransformer transformer = new EdgeWeightTransformer(this.graph);
		this.between = new BetweennessCentrality<Node, Edge>(this.graph, transformer);
	}
	
	public void run(){
		double max = Double.MIN_VALUE;
		double min = Double.MAX_VALUE;
		double max2 = Double.MIN_VALUE;
		double min2 = Double.MAX_VALUE;
		// Calculating Betweens
		for(Node v : this.graph.getVertices()){
			double score = this.between.getVertexScore(v);
			this.results.put(v.toString(), score);
			max = Math.max(max, score);
			min = Math.min(min, score);
		}
		//if(Betweenness.min == Double.MAX_VALUE){
			Betweenness.min = min;
			Betweenness.max = max;
		//}
		
		max2 = Double.MIN_VALUE;
		min2 = Double.MAX_VALUE;
		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - Betweenness.min)/(Betweenness.max - Betweenness.min);
			score = Math.max(0.001, score);
			score = Math.log(score);
			max2 = Math.max(max2, score);
			min2 = Math.min(min2, score);
			this.results.put(v.toString(), score);
		}
		//if(Betweenness.min2 == Double.MAX_VALUE){
			Betweenness.min2 = min2;
			Betweenness.max2 = max2;
		//}
			
		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - Betweenness.min2)/(Betweenness.max2 - Betweenness.min2);
			this.results.put(v.toString(), score);
		}
	}
	
}
