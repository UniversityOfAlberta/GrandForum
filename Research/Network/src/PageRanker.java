import edu.uci.ics.jung.algorithms.scoring.PageRank;
import edu.uci.ics.jung.graph.SparseMultigraph;

public class PageRanker extends Centrality {

	private static double max = Double.MIN_VALUE;
	private static double max2 = Double.MIN_VALUE;
	private static double min = Double.MAX_VALUE;
	private static double min2 = Double.MAX_VALUE;
	
	private PageRank<Node, Edge> pageRank;
	
	public PageRanker(SparseMultigraph<Node, Edge> graph){
		super(graph);
		this.pageRank = new PageRank<Node, Edge>(this.graph, 0.2);
		this.pageRank.acceptDisconnectedGraph(true);
	}
	
	public void run(){
		double max = Double.MIN_VALUE;
		double min = Double.MAX_VALUE;
		double max2 = Double.MIN_VALUE;
		double min2 = Double.MAX_VALUE;
		
		this.pageRank.evaluate();
		// Calculating PageRanks
		for(Node v : this.graph.getVertices()){
			double score = this.pageRank.getVertexScore(v);
			this.results.put(v.toString(), score);
			max = Math.max(max, score);
			min = Math.min(min, score);
		}
		//if(PageRanker.min == Double.MAX_VALUE){
			PageRanker.min = min;
			PageRanker.max = max;
		//}
		
		max2 = Double.MIN_VALUE;
		min2 = Double.MAX_VALUE;
		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - PageRanker.min)/(PageRanker.max - PageRanker.min);
			score = Math.max(0.001, score);
			score = Math.log(score);
			max2 = Math.max(max2, score);
			min2 = Math.min(min2, score);
			this.results.put(v.toString(), score);
		}
		//if(PageRanker.min2 == Double.MAX_VALUE){
			PageRanker.min2 = min2;
			PageRanker.max2 = max2;
		//}

		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - PageRanker.min2)/(PageRanker.max2 - PageRanker.min2);
			this.results.put(v.toString(), score);
		}
	}
	
}
