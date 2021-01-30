# cyber-duck-demo-module
Cyber Duck demo module for retrieving and displaying Spotify data. The module provides two methods of retrieving data:
1. A block - added via `admin/structure/block`, block name `Spotify artists`
1. A page - `/artist/{artist-name}?id=` - the page accepts an optional artist ID parameter. If the parameter is included the Spotify API will return a result based on artist ID (should be more accurate). The block provides this query string on links to the artist page by default. If no query parameter is present the page will return a result based on artist name.

# Module config
1. Go to `/admin/config/spotify`
1. Add application client and secret keys from the [Spotify developer dashboard](https://developer.spotify.com/dashboard)

# Module theme development
The module related CSS is compiled and committed, for any changes follow:
1. Go to `/styles` from the module root
1. Run `npm install`
1. Make any sass changes in the `/sass` folder 
1. Run `gulp compile` from the theme folder
