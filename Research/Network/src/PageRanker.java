import java.util.HashMap;

import edu.uci.ics.jung.algorithms.scoring.PageRank;
import edu.uci.ics.jung.graph.SparseMultigraph;

public class PageRanker extends Centrality {

	private static HashMap<String, Double> max = new HashMap<String, Double>();
	private static HashMap<String, Double> min = new HashMap<String, Double>();
	private static boolean firstTime = true;
	
	private PageRank<Node, Edge> pageRank;
	
	public PageRanker(SparseMultigraph<Node, Edge> graph){
		super(graph);
	}
	
	public void run(){
		this.pageRank = new PageRank<Node, Edge>(this.graph, new EdgeWeightTransformer(this.graph, false), 0.5);
		this.pageRank.acceptDisconnectedGraph(true);
		this.pageRank.setMaxIterations(3);
		HashMap<String, Double> max = new HashMap<String, Double>();
		HashMap<String, Double> min = new HashMap<String, Double>();
		
		// Calculating PageRanks
		this.pageRank.evaluate();
		double danglerSum = 0;
		for(Node v : this.graph.getVertices()){
			double score = this.pageRank.getVertexScore(v);
			this.results.put(v.toString(), score);
			if(v.degree() == 0){
				danglerSum += score;
			}
		}
		
		// http://wwwconference.org/www2007/posters/poster893.pdf
		double alpha = this.pageRank.getAlpha();
		double lowerBound = ((alpha + (1d - (alpha)))/this.graph.getVertexCount())*Math.max(0.0001, danglerSum);
		for(Node v : this.graph.getVertices()){
			if(v.degree() == 0){
				this.results.put(v.toString(), lowerBound);
			}
			double score = this.results.get(v.toString());
			score = score/lowerBound;
			this.results.put(v.toString(), Math.log10(1d+score));
		}

		for(Node v : this.graph.getVertices()){
			double score = this.results.get(v.toString());
			if(!max.containsKey(v.getType())){
				max.put(v.getType(), score);
				min.put(v.getType(), Math.log10(2d));
			}
			else{
				max.put(v.getType(), Math.max(max.get(v.getType()), score));
				min.put(v.getType(), Math.log10(2d));
			}
			if(PageRanker.firstTime || !Centrality.NORMALIZE){
				PageRanker.max.put(v.getType(), max.get(v.getType()));
				PageRanker.min.put(v.getType(), Math.log10(2d));
			}
		}

		for(Node v : this.graph.getVertices()){
			// Normalize
			double score = this.results.get(v.toString());
			score = (score - PageRanker.min.get(v.getType()))/(PageRanker.max.get(v.getType()) - PageRanker.min.get(v.getType()));
			if(Double.isNaN(score)){
				score = 0;
			}
			this.results.put(v.toString(), Math.max(0, score));
		}
		PageRanker.firstTime = false;
	}
}
