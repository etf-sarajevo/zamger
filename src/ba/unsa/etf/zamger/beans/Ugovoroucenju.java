package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * Ugovoroucenju generated by hbm2java
 */
public class Ugovoroucenju implements java.io.Serializable {

	private Integer id;
	private int student;
	private int akademskaGodina;
	private int studij;
	private int semestar;
	private int planStudija;

	public Ugovoroucenju() {
	}

	public Ugovoroucenju(int student, int akademskaGodina, int studij,
			int semestar, int planStudija) {
		this.student = student;
		this.akademskaGodina = akademskaGodina;
		this.studij = studij;
		this.semestar = semestar;
		this.planStudija = planStudija;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public int getStudent() {
		return this.student;
	}

	public void setStudent(int student) {
		this.student = student;
	}

	public int getAkademskaGodina() {
		return this.akademskaGodina;
	}

	public void setAkademskaGodina(int akademskaGodina) {
		this.akademskaGodina = akademskaGodina;
	}

	public int getStudij() {
		return this.studij;
	}

	public void setStudij(int studij) {
		this.studij = studij;
	}

	public int getSemestar() {
		return this.semestar;
	}

	public void setSemestar(int semestar) {
		this.semestar = semestar;
	}

	public int getPlanStudija() {
		return this.planStudija;
	}

	public void setPlanStudija(int planStudija) {
		this.planStudija = planStudija;
	}

}
