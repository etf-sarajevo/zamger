package ba.unsa.etf.zamger.resources;

import javax.ws.rs.GET;
import javax.ws.rs.Produces;
import javax.ws.rs.Path;
import javax.ws.rs.PathParam;
import javax.ws.rs.core.Context;
import javax.ws.rs.core.Response;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.oltu.oauth2.common.exception.OAuthProblemException;
import org.apache.oltu.oauth2.common.exception.OAuthSystemException;

import org.hibernate.Session;

import ba.unsa.etf.zamger.beans.Osoba;
import ba.unsa.etf.zamger.util.HibernateUtil;
import ba.unsa.etf.zamger.util.AuthUtil;

//The Java class will be hosted at the URI path "/osoba"
@Path("/person")
public class OsobaResource {
    
    @GET
    @Produces("text/json")
    public Response getList(@Context HttpServletRequest request) {
    	Response maybeResponse = AuthUtil.getInstance().verifyToken(request, "person");
    	if (maybeResponse != null)
    		return maybeResponse;
    	return Response.ok(AuthUtil.getInstance().getCurrentUser()).build();
    }
    
    @GET
    @Produces("text/json")
    @Path(value="{id}")
    public Response getOsoba(@PathParam("id") int id, @Context HttpServletRequest request) {
    	Response maybeResponse = AuthUtil.getInstance().verifyToken(request, "person");
    	if (maybeResponse != null)
    		return maybeResponse;
    	// Require that user is authenticated
    	if (AuthUtil.getInstance().getCurrentUser() == null)
    		return AuthUtil.buildForbiddenResponse();
    	
    	// Access control
    	if (!(AuthUtil.getInstance().getCurrentUser().getId() == id) &&
    			!AuthUtil.getInstance().checkAcl("siteadmin") &&
    			!AuthUtil.getInstance().checkAcl("studentska"))
    		return false;
    	
    	// TODO: Osoba should be broken up into different levels of person info
    	// different classes of users should be allowed access to different levels
    	
		Session session = HibernateUtil.getSessionFactory().openSession();
		Object o = session.get(Osoba.class, id);
		if (o == null)
			return Response.status(HttpServletResponse.SC_NOT_FOUND).build();
		return Response.ok((Osoba)o).build();
    }	
}
