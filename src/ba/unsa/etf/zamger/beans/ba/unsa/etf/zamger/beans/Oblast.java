package ba.unsa.etf.zamger.beans;

// Generated May 26, 2015 12:09:40 PM by Hibernate Tools 3.4.0.CR1

/**
 * Oblast generated by hbm2java
 */
public class Oblast implements java.io.Serializable {

	private Integer id;
	private Institucija institucija;
	private String naziv;

	public Oblast() {
	}

	public Oblast(Institucija institucija, String naziv) {
		this.institucija = institucija;
		this.naziv = naziv;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public Institucija getInstitucija() {
		return this.institucija;
	}

	public void setInstitucija(Institucija institucija) {
		this.institucija = institucija;
	}

	public String getNaziv() {
		return this.naziv;
	}

	public void setNaziv(String naziv) {
		this.naziv = naziv;
	}

}
