package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * BbPostText generated by hbm2java
 */
public class BbPostText implements java.io.Serializable {

	private int post;
	private BbPost bbPost;
	private String tekst;

	public BbPostText() {
	}

	public BbPostText(BbPost bbPost, String tekst) {
		this.bbPost = bbPost;
		this.tekst = tekst;
	}

	public int getPost() {
		return this.post;
	}

	public void setPost(int post) {
		this.post = post;
	}

	public BbPost getBbPost() {
		return this.bbPost;
	}

	public void setBbPost(BbPost bbPost) {
		this.bbPost = bbPost;
	}

	public String getTekst() {
		return this.tekst;
	}

	public void setTekst(String tekst) {
		this.tekst = tekst;
	}

}
