import java.util.HashMap;

import edu.uci.ics.jung.algorithms.scoring.BetweennessCentrality;

import edu.uci.ics.jung.graph.SparseMultigraph;

public class Betweenness extends Centrality {

	private static HashMap<String, Double> max = new HashMap<String, Double>();
	private static HashMap<String, Double> max2 = new HashMap<String, Double>();
	private static HashMap<String, Double> min = new HashMap<String, Double>();
	private static HashMap<String, Double> min2 = new HashMap<String, Double>();
	private static boolean firstTime = true;
	
	private BetweennessCentrality<Node, Edge> between;
	
	public Betweenness(SparseMultigraph<Node, Edge> graph){
		super(graph);
	}
	
	public void run(){
		EdgeWeightTransformer transformer = new EdgeWeightTransformer(this.graph);
		this.between = new BetweennessCentrality<Node, Edge>(this.graph, transformer);
		HashMap<String, Double> max = new HashMap<String, Double>();
		HashMap<String, Double> min = new HashMap<String, Double>();
		HashMap<String, Double> max2 = new HashMap<String, Double>();
		HashMap<String, Double> min2 = new HashMap<String, Double>();
		// Calculating Betweens
		for(Node v : this.graph.getVertices()){
			double score = this.between.getVertexScore(v);
			this.results.put(v.toString(), score);
			if(!max.containsKey(v.getType())){
				max.put(v.getType(), score);
				min.put(v.getType(), score);
			}
			else{
				max.put(v.getType(), Math.max(max.get(v.getType()), score));
				min.put(v.getType(), Math.min(min.get(v.getType()), score));
			}
			if(Betweenness.firstTime || !Centrality.NORMALIZE){
				Betweenness.max.put(v.getType(), max.get(v.getType()));
				Betweenness.min.put(v.getType(), min.get(v.getType()));
			}
		}
		
		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - Betweenness.min.get(v.getType()))/(Betweenness.max.get(v.getType()) - Betweenness.min.get(v.getType()));
			score = Math.max(0.001, score);
			score = Math.log(score);
			if(!max2.containsKey(v.getType())){
				max2.put(v.getType(), score);
				min2.put(v.getType(), score);
			}
			else{
				max2.put(v.getType(), Math.max(max2.get(v.getType()), score));
				min2.put(v.getType(), Math.min(min2.get(v.getType()), score));
			}
			if(Betweenness.firstTime || !Centrality.NORMALIZE){
				Betweenness.max2.put(v.getType(), max2.get(v.getType()));
				Betweenness.min2.put(v.getType(), min2.get(v.getType()));
			}
			this.results.put(v.toString(), score);
		}
			
		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - Betweenness.min2.get(v.getType()))/(Betweenness.max2.get(v.getType()) - Betweenness.min2.get(v.getType()));
			this.results.put(v.toString(), score);
		}
		Betweenness.firstTime = false;
	}
	
}
