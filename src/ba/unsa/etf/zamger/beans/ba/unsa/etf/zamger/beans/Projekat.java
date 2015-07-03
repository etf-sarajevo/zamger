package ba.unsa.etf.zamger.beans;

// Generated May 26, 2015 12:09:40 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;
import java.util.HashSet;
import java.util.Set;

/**
 * Projekat generated by hbm2java
 */
public class Projekat implements java.io.Serializable {

	private int id;
	private Predmet predmet;
	private AkademskaGodina akademskaGodina;
	private String naziv;
	private String opis;
	private Date vrijeme;
	private String biljeska;
	private Set<BbTema> bbTemas = new HashSet<BbTema>(0);
	private Set<BlClanak> blClanaks = new HashSet<BlClanak>(0);

	public Projekat() {
	}

	public Projekat(int id, Predmet predmet, AkademskaGodina akademskaGodina,
			String naziv, String opis, Date vrijeme, String biljeska) {
		this.id = id;
		this.predmet = predmet;
		this.akademskaGodina = akademskaGodina;
		this.naziv = naziv;
		this.opis = opis;
		this.vrijeme = vrijeme;
		this.biljeska = biljeska;
	}

	public Projekat(int id, Predmet predmet, AkademskaGodina akademskaGodina,
			String naziv, String opis, Date vrijeme, String biljeska,
			Set<BbTema> bbTemas, Set<BlClanak> blClanaks) {
		this.id = id;
		this.predmet = predmet;
		this.akademskaGodina = akademskaGodina;
		this.naziv = naziv;
		this.opis = opis;
		this.vrijeme = vrijeme;
		this.biljeska = biljeska;
		this.bbTemas = bbTemas;
		this.blClanaks = blClanaks;
	}

	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public Predmet getPredmet() {
		return this.predmet;
	}

	public void setPredmet(Predmet predmet) {
		this.predmet = predmet;
	}

	public AkademskaGodina getAkademskaGodina() {
		return this.akademskaGodina;
	}

	public void setAkademskaGodina(AkademskaGodina akademskaGodina) {
		this.akademskaGodina = akademskaGodina;
	}

	public String getNaziv() {
		return this.naziv;
	}

	public void setNaziv(String naziv) {
		this.naziv = naziv;
	}

	public String getOpis() {
		return this.opis;
	}

	public void setOpis(String opis) {
		this.opis = opis;
	}

	public Date getVrijeme() {
		return this.vrijeme;
	}

	public void setVrijeme(Date vrijeme) {
		this.vrijeme = vrijeme;
	}

	public String getBiljeska() {
		return this.biljeska;
	}

	public void setBiljeska(String biljeska) {
		this.biljeska = biljeska;
	}

	public Set<BbTema> getBbTemas() {
		return this.bbTemas;
	}

	public void setBbTemas(Set<BbTema> bbTemas) {
		this.bbTemas = bbTemas;
	}

	public Set<BlClanak> getBlClanaks() {
		return this.blClanaks;
	}

	public void setBlClanaks(Set<BlClanak> blClanaks) {
		this.blClanaks = blClanaks;
	}

}
