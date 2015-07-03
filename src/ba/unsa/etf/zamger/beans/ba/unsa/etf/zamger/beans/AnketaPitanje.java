package ba.unsa.etf.zamger.beans;

// Generated May 26, 2015 12:09:40 PM by Hibernate Tools 3.4.0.CR1

import java.util.HashSet;
import java.util.Set;

/**
 * AnketaPitanje generated by hbm2java
 */
public class AnketaPitanje implements java.io.Serializable {

	private Integer id;
	private AnketaTipPitanja anketaTipPitanja;
	private AnketaAnketa anketaAnketa;
	private String tekst;
	private Set<AnketaOdgovorRank> anketaOdgovorRanks = new HashSet<AnketaOdgovorRank>(
			0);
	private Set<AnketaIzboriPitanja> anketaIzboriPitanjas = new HashSet<AnketaIzboriPitanja>(
			0);
	private Set<AnketaOdgovorDopisani> anketaOdgovorDopisanis = new HashSet<AnketaOdgovorDopisani>(
			0);
	private Set<AnketaOdgovorIzbori> anketaOdgovorIzboris = new HashSet<AnketaOdgovorIzbori>(
			0);
	private Set<AnketaOdgovorText> anketaOdgovorTexts = new HashSet<AnketaOdgovorText>(
			0);

	public AnketaPitanje() {
	}

	public AnketaPitanje(AnketaTipPitanja anketaTipPitanja,
			AnketaAnketa anketaAnketa, String tekst) {
		this.anketaTipPitanja = anketaTipPitanja;
		this.anketaAnketa = anketaAnketa;
		this.tekst = tekst;
	}

	public AnketaPitanje(AnketaTipPitanja anketaTipPitanja,
			AnketaAnketa anketaAnketa, String tekst,
			Set<AnketaOdgovorRank> anketaOdgovorRanks,
			Set<AnketaIzboriPitanja> anketaIzboriPitanjas,
			Set<AnketaOdgovorDopisani> anketaOdgovorDopisanis,
			Set<AnketaOdgovorIzbori> anketaOdgovorIzboris,
			Set<AnketaOdgovorText> anketaOdgovorTexts) {
		this.anketaTipPitanja = anketaTipPitanja;
		this.anketaAnketa = anketaAnketa;
		this.tekst = tekst;
		this.anketaOdgovorRanks = anketaOdgovorRanks;
		this.anketaIzboriPitanjas = anketaIzboriPitanjas;
		this.anketaOdgovorDopisanis = anketaOdgovorDopisanis;
		this.anketaOdgovorIzboris = anketaOdgovorIzboris;
		this.anketaOdgovorTexts = anketaOdgovorTexts;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public AnketaTipPitanja getAnketaTipPitanja() {
		return this.anketaTipPitanja;
	}

	public void setAnketaTipPitanja(AnketaTipPitanja anketaTipPitanja) {
		this.anketaTipPitanja = anketaTipPitanja;
	}

	public AnketaAnketa getAnketaAnketa() {
		return this.anketaAnketa;
	}

	public void setAnketaAnketa(AnketaAnketa anketaAnketa) {
		this.anketaAnketa = anketaAnketa;
	}

	public String getTekst() {
		return this.tekst;
	}

	public void setTekst(String tekst) {
		this.tekst = tekst;
	}

	public Set<AnketaOdgovorRank> getAnketaOdgovorRanks() {
		return this.anketaOdgovorRanks;
	}

	public void setAnketaOdgovorRanks(Set<AnketaOdgovorRank> anketaOdgovorRanks) {
		this.anketaOdgovorRanks = anketaOdgovorRanks;
	}

	public Set<AnketaIzboriPitanja> getAnketaIzboriPitanjas() {
		return this.anketaIzboriPitanjas;
	}

	public void setAnketaIzboriPitanjas(
			Set<AnketaIzboriPitanja> anketaIzboriPitanjas) {
		this.anketaIzboriPitanjas = anketaIzboriPitanjas;
	}

	public Set<AnketaOdgovorDopisani> getAnketaOdgovorDopisanis() {
		return this.anketaOdgovorDopisanis;
	}

	public void setAnketaOdgovorDopisanis(
			Set<AnketaOdgovorDopisani> anketaOdgovorDopisanis) {
		this.anketaOdgovorDopisanis = anketaOdgovorDopisanis;
	}

	public Set<AnketaOdgovorIzbori> getAnketaOdgovorIzboris() {
		return this.anketaOdgovorIzboris;
	}

	public void setAnketaOdgovorIzboris(
			Set<AnketaOdgovorIzbori> anketaOdgovorIzboris) {
		this.anketaOdgovorIzboris = anketaOdgovorIzboris;
	}

	public Set<AnketaOdgovorText> getAnketaOdgovorTexts() {
		return this.anketaOdgovorTexts;
	}

	public void setAnketaOdgovorTexts(Set<AnketaOdgovorText> anketaOdgovorTexts) {
		this.anketaOdgovorTexts = anketaOdgovorTexts;
	}

}
