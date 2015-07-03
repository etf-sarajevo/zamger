package ba.unsa.etf.zamger.util;

import java.util.HashSet;
import java.util.Set;
import java.util.List;

import javax.ws.rs.core.Response;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.oltu.oauth2.common.OAuth;
import org.apache.oltu.oauth2.as.response.OAuthASResponse;
import org.apache.oltu.oauth2.rs.response.OAuthRSResponse;
import org.apache.oltu.oauth2.common.error.OAuthError;
import org.apache.oltu.oauth2.common.exception.OAuthProblemException;
import org.apache.oltu.oauth2.common.exception.OAuthSystemException;
import org.apache.oltu.oauth2.common.message.OAuthResponse;
import org.apache.oltu.oauth2.common.message.types.ParameterStyle;
import org.apache.oltu.oauth2.common.utils.OAuthUtils;
import org.apache.oltu.oauth2.rs.request.OAuthAccessResourceRequest;
import org.hibernate.Session;
import org.hibernate.Query;

import ba.unsa.etf.zamger.beans.Acl;
import ba.unsa.etf.zamger.beans.Auth;
import ba.unsa.etf.zamger.beans.Osoba;
import ba.unsa.etf.zamger.beans.WsOauthRequest;
import ba.unsa.etf.zamger.beans.WsSession;

public class AuthUtil {
    
    public static final String INVALID_CLIENT_DESCRIPTION = "Client authentication failed (e.g., unknown client, no client authentication included, or unsupported authentication method).";
	
    private Set<String> authCodes = new HashSet<>();
    private Set<String> tokens = new HashSet<>();
    private Osoba currentUser = null;

    // Singleton pattern
	private static AuthUtil instance = null;
	protected AuthUtil() {
	      // Exists only to defeat instantiation.
   }
   public static AuthUtil getInstance() {
      if(instance == null) {
         instance = new AuthUtil();
      }
      return instance;
   }
	   
	   
    /*public void addAuthCode(String authCode) {
        authCodes.add(authCode);
    }*/

    public static Osoba getPersonForAuthCode(String authCode) throws MissingAuthCodeException {
		Session session = HibernateUtil.getSessionFactory().openSession();
		String hql = "FROM WsOauthRequest WHERE code = :code";
		Query query = session.createQuery(hql);
		query.setParameter("code", authCode);
		List results = query.list();
		if (results.isEmpty()) throw new MissingAuthCodeException();
		WsOauthRequest request = (WsOauthRequest)results.get(0); 
		return request.getOsoba(); // Should never return >1 results
    }

   /* public void addToken(String token) {
        tokens.add(token);
    }
    
    public boolean isValidToken(String token) {
        return tokens.contains(token);
    }*/
    
    public Osoba getCurrentUser() {
    	return currentUser;
    }
    
    public boolean checkAcl(String aclName) {
    	Set<Acl> acls = currentUser.getAcls();
    	for (Acl acl : acls) {
    		if (acl.getAclTip().getNaziv() == aclName) return true;
    	}
    	return false;
    }

    
    public boolean checkAcl(String aclName, int attribute1) {
    	Set<Acl> acls = currentUser.getAcls();
    	for (Acl acl : acls) {
    		if (acl.getAclTip().getNaziv() == aclName) {
    			if (acl.getAclTip().getBrPredikata() > 0 && acl.getId().getPredikat1() == attribute1) return true;
    		}
    	}
    	return false;
    }
    
    public boolean checkAcl(String aclName, int attribute1, int attribute2) {
    	Set<Acl> acls = currentUser.getAcls();
    	for (Acl acl : acls) {
    		if (acl.getAclTip().getNaziv() == aclName) {
    			if (acl.getAclTip().getBrPredikata() > 1 && 
    					acl.getId().getPredikat1() == attribute1 &&
    					acl.getId().getPredikat2() == attribute2) return true;
    		}
    	}
    	return false;
    }
    
    public boolean checkAcl(String aclName, int attribute1, int attribute2, int attribute3) {
    	Set<Acl> acls = currentUser.getAcls();
    	for (Acl acl : acls) {
    		if (acl.getAclTip().getNaziv() == aclName) {
    			if (acl.getAclTip().getBrPredikata() == 3 && 
    					acl.getId().getPredikat1() == attribute1 &&
    					acl.getId().getPredikat2() == attribute2 &&
    					acl.getId().getPredikat3() == attribute3) return true;
    		}
    	}
    	return false;
    }
    
    public static Osoba getPersonForUserPassword(String user, String pass) {
		Session session = HibernateUtil.getSessionFactory().openSession();
		String hql = "FROM Auth WHERE login = :user AND password = :pass";
		Query query = session.createQuery(hql);
		query.setParameter("user", user);
		query.setParameter("pass", pass);
		List list = query.list();
        if (list.isEmpty()) return (Osoba)null;
        Auth auth = (Auth)list.get(0);
        return auth.getOsoba();
    }
    
    public Response verifyToken(HttpServletRequest request, String resourceName) {
    	try {
	    	try {
		    	OAuthAccessResourceRequest oauthRequest = new OAuthAccessResourceRequest(request, ParameterStyle.HEADER);
		    	String accessToken = oauthRequest.getAccessToken();
	    		Session session = HibernateUtil.getSessionFactory().openSession();
	    		String hql = "FROM WsSession WHERE token = :token";
	    		Query query = session.createQuery(hql);
	    		query.setParameter("token", accessToken);
	    		List list = query.list();
	    		if (list.isEmpty()) return buildInvalidTokenResponse(resourceName);
	    		
	    		WsSession sess = (WsSession)list.get(0);
	    		currentUser = sess.getOsoba();
	    		return (Response)null;
	    		
	    	} catch(OAuthProblemException e) {
	    		return buildProblemTokenResponse(resourceName, e);
	    	}
    	} catch(OAuthSystemException e) {
    		// Shouldn't happen 
    		return (Response)null;
    	}
    }
    
    public static void addAuthzCode(Osoba osoba, String code) {
        Session session = HibernateUtil.getSessionFactory().openSession();
        WsOauthRequest req = new WsOauthRequest(osoba, code);
        session.beginTransaction();
        session.save(req);
        session.getTransaction().commit();	
    }
    
    public static void addAccessToken(Osoba osoba, String accessToken) {
        Session session = HibernateUtil.getSessionFactory().openSession();
        WsSession sess = new WsSession(osoba, accessToken);
        session.beginTransaction();
        session.save(sess);
        session.getTransaction().commit();
    }
    
    public static boolean addAccessTokenForAuth(String code, String accessToken) {
    	Session session = HibernateUtil.getSessionFactory().openSession();
		String hql = "FROM WsOauthRequest WHERE code = :code";
		Query query = session.createQuery(hql);
		query.setParameter("code", code);
		List list = query.list();
		System.out.println("Rezultati drugog upita: "+list.toString());
		if (list.isEmpty()) return false;
		WsOauthRequest req = (WsOauthRequest) list.get(0);
		addAccessToken(req.getOsoba(), accessToken);
		return true;
    }
    
    public static void addAccessTokenForUsername(String login, String accessToken) {
       	Session session = HibernateUtil.getSessionFactory().openSession();
		String hql = "SELECT osoba FROM auth, authid WHERE authid.login = :login AND authid.id=auth.id";
		Query query = session.createQuery(hql);
		query.setParameter("login", login);
		List<Osoba> list = query.list();
		addAccessToken(list.get(0), accessToken);    	
    }

    public static Response buildInvalidClientIdResponse() throws OAuthSystemException {
        OAuthResponse response =
                OAuthASResponse.errorResponse(HttpServletResponse.SC_BAD_REQUEST)
                .setError(OAuthError.TokenResponse.INVALID_CLIENT)
                .setErrorDescription(INVALID_CLIENT_DESCRIPTION)
                .buildJSONMessage();
        return Response.status(response.getResponseStatus()).entity(response.getBody()).build();
    }

    public static Response buildInvalidClientSecretResponse() throws OAuthSystemException {
        OAuthResponse response =
                OAuthASResponse.errorResponse(HttpServletResponse.SC_UNAUTHORIZED)
                .setError(OAuthError.TokenResponse.UNAUTHORIZED_CLIENT).setErrorDescription(INVALID_CLIENT_DESCRIPTION)
                .buildJSONMessage();
        return Response.status(response.getResponseStatus()).entity(response.getBody()).build();
    }

    public static  Response buildBadAuthCodeResponse() throws OAuthSystemException {
        OAuthResponse response = OAuthASResponse
                .errorResponse(HttpServletResponse.SC_BAD_REQUEST)
                .setError(OAuthError.TokenResponse.INVALID_GRANT)
                .setErrorDescription("invalid authorization code")
                .buildJSONMessage();
        return Response.status(response.getResponseStatus()).entity(response.getBody()).build();
    }

    public static  Response buildInvalidUserPassResponse() throws OAuthSystemException {
        OAuthResponse response = OAuthASResponse
                .errorResponse(HttpServletResponse.SC_BAD_REQUEST)
                .setError(OAuthError.TokenResponse.INVALID_GRANT)
                .setErrorDescription("invalid username or password")
                .buildJSONMessage();
        return Response.status(response.getResponseStatus()).entity(response.getBody()).build();
    }
    

    public static  Response buildInvalidTokenResponse(String resourceName) throws OAuthSystemException {
    	OAuthResponse oauthResponse = OAuthRSResponse
                .errorResponse(HttpServletResponse.SC_UNAUTHORIZED)
                .setRealm(resourceName)
                .setError(OAuthError.ResourceResponse.INVALID_TOKEN)
                .buildHeaderMessage();

        //return Response.status(Response.Status.UNAUTHORIZED).build();
        return Response.status(Response.Status.UNAUTHORIZED)
                .header(OAuth.HeaderType.WWW_AUTHENTICATE,
                oauthResponse.getHeader(OAuth.HeaderType.WWW_AUTHENTICATE))
                .build();
    }

    public static  Response buildProblemTokenResponse(String resourceName, OAuthProblemException e) throws OAuthSystemException {
        // Check if the error code has been set
        String errorCode = e.getError();
        if (OAuthUtils.isEmpty(errorCode)) {
        	// Empty message happens often i.e. if wrong authentication method is sent ("OAuth2" instead of "Bearer")

            // Return the OAuth error message
            OAuthResponse oauthResponse = OAuthRSResponse
                    .errorResponse(HttpServletResponse.SC_UNAUTHORIZED)
                    .setRealm(resourceName)
                    .buildHeaderMessage();

            // If no error code then return a standard 401 Unauthorized response
            return Response.status(Response.Status.UNAUTHORIZED)
                    .header(OAuth.HeaderType.WWW_AUTHENTICATE,
                    oauthResponse.getHeader(OAuth.HeaderType.WWW_AUTHENTICATE))
                    .build();
        }

        OAuthResponse oauthResponse = OAuthRSResponse
                .errorResponse(HttpServletResponse.SC_UNAUTHORIZED)
                .setRealm(resourceName)
                .setError(e.getError())
                .setErrorDescription(e.getDescription())
                .setErrorUri(e.getUri())
                .buildHeaderMessage();

        return Response.status(Response.Status.BAD_REQUEST)
                .header(OAuth.HeaderType.WWW_AUTHENTICATE, oauthResponse.getHeader(OAuth.HeaderType.WWW_AUTHENTICATE))
                .build();    	
    }


    public static Response buildForbiddenResponse() {
        return Response.status(Response.Status.FORBIDDEN)
                .build();    	
    }
}
