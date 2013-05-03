import java.util.HashMap;

import edu.uci.ics.jung.algorithms.scoring.PageRank;
import edu.uci.ics.jung.graph.SparseMultigraph;

public class PageRanker extends Centrality {

	private static HashMap<String, Double> max = new HashMap<String, Double>();
	private static HashMap<String, Double> max2 = new HashMap<String, Double>();
	private static HashMap<String, Double> min = new HashMap<String, Double>();
	private static HashMap<String, Double> min2 = new HashMap<String, Double>();
	private static boolean firstTime = true;
	
	private PageRank<Node, Edge> pageRank;
	
	public PageRanker(SparseMultigraph<Node, Edge> graph){
		super(graph);
	}
	
	public void run(){
		this.pageRank = new PageRank<Node, Edge>(this.graph, 0.5);
		this.pageRank.acceptDisconnectedGraph(true);
		this.pageRank.setMaxIterations(10);
		HashMap<String, Double> max = new HashMap<String, Double>();
		HashMap<String, Double> min = new HashMap<String, Double>();
		HashMap<String, Double> max2 = new HashMap<String, Double>();
		HashMap<String, Double> min2 = new HashMap<String, Double>();
		
		this.pageRank.evaluate();
		// Calculating PageRanks
		for(Node v : this.graph.getVertices()){
			double score = this.pageRank.getVertexScore(v)*Math.max(1, v.degree());
			this.results.put(v.toString(), score);
			if(!max.containsKey(v.getType())){
				max.put(v.getType(), score);
				min.put(v.getType(), score);
			}
			else{
				max.put(v.getType(), Math.max(max.get(v.getType()), score));
				min.put(v.getType(), Math.min(min.get(v.getType()), score));
			}
			if(PageRanker.firstTime || !Centrality.NORMALIZE){
				PageRanker.max.put(v.getType(), max.get(v.getType()));
				PageRanker.min.put(v.getType(), min.get(v.getType()));
			}
		}

		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - PageRanker.min.get(v.getType()))/(PageRanker.max.get(v.getType()) - PageRanker.min.get(v.getType()));
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
			if(PageRanker.firstTime || !Centrality.NORMALIZE){
				PageRanker.max2.put(v.getType(), max2.get(v.getType()));
				PageRanker.min2.put(v.getType(), min2.get(v.getType()));
			}
			this.results.put(v.toString(), score);
		}
		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - PageRanker.min2.get(v.getType()))/(PageRanker.max2.get(v.getType()) - PageRanker.min2.get(v.getType()));
			this.results.put(v.toString(), score);
		}
		PageRanker.firstTime = false;
	}
}
