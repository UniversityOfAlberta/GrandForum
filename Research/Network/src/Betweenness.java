import java.util.HashMap;

import edu.uci.ics.jung.algorithms.scoring.BetweennessCentrality;

import edu.uci.ics.jung.graph.SparseMultigraph;

public class Betweenness extends Centrality {

	private static HashMap<String, Double> max = new HashMap<String, Double>();
	private static HashMap<String, Double> min = new HashMap<String, Double>();
	private static boolean firstTime = true;
	
	private BetweennessCentrality<Node, Edge> between;
	
	public Betweenness(SparseMultigraph<Node, Edge> graph){
		super(graph);
	}
	
	public void run(){
		this.between = new BetweennessCentrality<Node, Edge>(this.graph);
		HashMap<String, Double> max = new HashMap<String, Double>();
		HashMap<String, Double> min = new HashMap<String, Double>();
		// Calculating Betweens
		for(Node v : this.graph.getVertices()){
			if(v.degree() == 0) continue;
			double score = this.between.getVertexScore(v);
			double degree = v.degree();
			score = (score*degree);
			score = Math.log(1+(score*2));
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
			if(v.degree() == 0) continue;
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - Betweenness.min.get(v.getType()))/(Betweenness.max.get(v.getType()) - Betweenness.min.get(v.getType()));
			this.results.put(v.toString(), Math.max(0, score));
		}
		Betweenness.firstTime = false;
	}
	
}
