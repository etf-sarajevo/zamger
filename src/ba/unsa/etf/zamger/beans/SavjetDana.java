package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * SavjetDana generated by hbm2java
 */
public class SavjetDana implements java.io.Serializable {

	private Integer id;
	private String tekst;
	private String vrstaKorisnika;

	public SavjetDana() {
	}

	public SavjetDana(String tekst, String vrstaKorisnika) {
		this.tekst = tekst;
		this.vrstaKorisnika = vrstaKorisnika;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public String getTekst() {
		return this.tekst;
	}

	public void setTekst(String tekst) {
		this.tekst = tekst;
	}

	public String getVrstaKorisnika() {
		return this.vrstaKorisnika;
	}

	public void setVrstaKorisnika(String vrstaKorisnika) {
		this.vrstaKorisnika = vrstaKorisnika;
	}

}
