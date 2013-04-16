import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.MalformedURLException;
import java.net.URI;
import java.net.URISyntaxException;
import java.util.HashMap;
import java.util.Vector;
import org.apache.http.HttpResponse;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.client.utils.URIBuilder;

import org.json.*;

import edu.uci.ics.jung.graph.Graph;
import edu.uci.ics.jung.graph.SparseMultigraph;
import edu.uci.ics.jung.graph.util.EdgeType;

public class Network {
	
	public static String DOMAIN = "https://forum.grand-nce.ca/index.php";
	
	public static final int BETWEEN = 0;
	public static final int CLOSENESS = 1;
	public static final int PAGERANK = 2;
	
	private String url;
	private String id;
	private String year;
	private Vector<String> nodeTypes;
	private HashMap<String, HashMap<String, String>> metas;
	private SparseMultigraph<Node, Edge> graph;
	private HashMap<String, Node> nodes;
	private Vector<HashMap<String, Double>> results;
	private Vector<Vector<String>> ranks;
	
	/*
	 * Creates a new Network of Nodes and Edges
	 */
	public Network(String url, String year, String id){
		this.url = url;
		this.year = year;
		this.id = id;
		this.nodeTypes = new Vector<String>();
		this.nodes = new HashMap<String, Node>();
		this.metas = new HashMap<String, HashMap<String,String>>();
		this.results = new Vector<HashMap<String, Double>>();
		this.ranks = new Vector<Vector<String>>();
	}
	
	public void calc(){
		this.fetchJSON();
		System.out.println("    Calculating");
		Betweenness between = new Betweenness(this.graph);
		Closeness closeness = new Closeness(this.graph);
		PageRanker pageRanker = new PageRanker(this.graph);
		between.start();
		closeness.start();
		pageRanker.start();
		
		try {
			between.join();
			closeness.join();
			pageRanker.join();
		} catch (InterruptedException e) {
			e.printStackTrace();
		}
		
		this.results.add(Network.BETWEEN, between.getResults());
		this.results.add(Network.CLOSENESS, closeness.getResults());
		this.results.add(Network.PAGERANK, pageRanker.getResults());
		
		//this.ranks.insertElementAt(between.getRanks(), Network.BETWEEN);
		//this.ranks.insertElementAt(closeness.getRanks(), Network.CLOSENESS);
		//this.ranks.insertElementAt(pageRanker.getRanks(), Network.PAGERANK);
	}
	
	public Graph<Node, Edge> getGraph(){
		return this.graph;
	}

	public void setUrl(String url){
		this.url = url;
	}
	
	public Vector<HashMap<String, Double>> getResults(){
		return this.results;
	}
	
	public Vector<Vector<String>> getRanks(String type){
		Vector<Vector<String>> ranks = new Vector<Vector<String>>();
		int i = 0;
		for(Vector<String> r : this.ranks){
			ranks.add(new Vector<String>());
			for(String key : r){
				Node node = this.nodes.get(key);
				if(node.getType().equals(type)){
					ranks.get(i).add(key);
				}
			}
			i++;
		}
		return ranks;
	}
	
	public HashMap<String,HashMap<String,String>> getMetas(){
		return this.metas;
	}
	
	public HashMap<String, Node> getNodes(){
		return this.nodes;
	}
	
	public HashMap<String, Node> getNodes(String type){
		HashMap<String, Node> nodes = new HashMap<String, Node>();
		for(String key : this.nodes.keySet()){
			Node node = this.nodes.get(key);
			if(node.getType().equals(type)){
				nodes.put(key, node);
			}
		}
		return nodes;
	}
	
	public Vector<String> getMetaLabels(String type){
		for(String n : this.nodes.keySet()){
			Node node = this.nodes.get(n);
			if(node.getType().equals(type)){
				Vector<String> labels = new Vector<String>();
				for(String m : this.metas.get(n).keySet()){
					labels.add(m);
				}
				return labels;
			}
		}
		return new Vector<String>();
	}
	
	public Vector<String> getNodeTypes(){
		return this.nodeTypes;
	}

	/*
	 * Fetches the json from this.url and creates a new SparseMultiGraph (this.graph)
	 */
	public void fetchJSON(){
		try {
			Edge.clearCache();
			BufferedReader in;
			File file = new File("cache/" + this.id + ".json");
			if(Network.DOMAIN.compareTo("") != 0){
				HttpClient client = new DefaultHttpClient();
				URIBuilder builder = new URIBuilder();
				String scheme = "http";
				if(this.url.startsWith("https://")){
					scheme = "https";
					this.url = this.url.replace("https://", "");
				}
				else{
					this.url = this.url.replace("http://", "");
				}
				builder.setScheme(scheme);
				int i = 0;
				for(String split : this.url.split("\\?")){
					if(i > 0){
						for(String split2 : split.split("\\&")){
							String[] splits3 = split2.split("=");
							String key = splits3[0];
							if(splits3.length == 2){
								String value = splits3[1];
								builder.addParameter(key, value);
							}
							else{
								builder.addParameter(key, "");
							}
						}
					}
					else{
						builder.setHost(split);
					}
					i++;
				}
				builder.setParameter("year", this.year);
				URI uri = builder.build();
				HttpGet request = new HttpGet(uri);
				System.out.println("  Fetching " + request.getURI());
				HttpResponse response = client.execute(request);
				in = new BufferedReader (new InputStreamReader(response.getEntity().getContent()));
			}
			else{
			    System.out.println("  Fetching Cache " + this.id + ".json");
				in = new BufferedReader(new FileReader(file));
			}
		    String string = "";
		    String str;
		    while ((str = in.readLine()) != null) {
		        string += str;
		    }
			
		    FileWriter fw = new FileWriter(file.getAbsoluteFile());
			BufferedWriter bw = new BufferedWriter(fw);
			bw.write(string);
			bw.close();
		    
			JSONObject json = new JSONObject(string);
			
			this.graph = new SparseMultigraph<Node, Edge>();
			
			JSONArray nodes = (JSONArray)json.get("nodes");
			JSONArray edges = (JSONArray)json.get("edges");
			
			for(int i = 0; i < nodes.length(); i++){
				// Adding Nodes
				JSONObject node = nodes.getJSONObject(i);
				Node n = new Node(node.getString("name"), node.getString("type"));
				this.metas.put(node.getString("name"), new HashMap<String,String>());
				this.graph.addVertex(n);
				this.nodes.put(node.getString("name"), n);
				if(!this.nodeTypes.contains(node.getString("type"))){
					this.nodeTypes.add(node.getString("type"));
				}
				
				// Adding Metas
				JSONObject meta = node.getJSONObject("meta");
				
				for(int j = 0; j < meta.length(); j++){
					String key = meta.names().get(j).toString();
					if(!key.equals("name")){
						this.metas.get(node.getString("name")).put(key, meta.getString(key));
					}
				}
			}

			for(int i = 0; i < edges.length(); i++){
				// Adding Edges
				JSONObject edge = (JSONObject)edges.get(i);
				
				Node a = this.nodes.get(edge.get("a"));
				Node b = this.nodes.get(edge.get("b"));
				if(b == null){
					b = new Node((String)edge.get("b"), "");
					this.nodes.put((String)edge.get("b"), b);
				}
				
				Edge e = new Edge(a, b);
				this.graph.addEdge(e, a, b, EdgeType.UNDIRECTED);
			}
			
			System.out.println("    #Nodes: " + this.graph.getVertexCount());
			System.out.println("    #Edges: " + this.graph.getEdgeCount());
		} catch (URISyntaxException e) {
			e.printStackTrace();
			System.err.println("There was a syntax error with the url");
		} catch (JSONException e) {
			System.err.println("There was a problem parsing the json");
			e.printStackTrace();
			System.exit(-1);
		} catch (MalformedURLException e){
			System.err.println("There was a problem loading the url");
			System.exit(-2);
		} catch (IOException e){
			System.err.println("There was a problem loading the url");
			System.exit(-2);
		}
	}
	
	public static void computeNetworks(String year){		
		Network all = new Network(Network.DOMAIN, year, year);
		NetworkManager allManager = new NetworkManager(year, all);
		allManager.calc();
		allManager.printCSV();
	}
	
	public static void main(String [] args){
		if(args.length >= 2){
			Integer startYear = Integer.parseInt(args[0]);
			Integer endYear = Integer.parseInt(args[1]);
			
			if(args.length == 3){
				Network.DOMAIN = args[2];
			}
			else{
				Network.DOMAIN = "";
			}
			
			for(int i = startYear; i <= endYear; i++){
				Network.computeNetworks(new Integer(i).toString());
			}
		}
		else{
			System.out.println("Usage: java - jar Network.jar startYear endYear [domain]");
		}
	}
	
}
