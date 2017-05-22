# PS Forms

A very simply development platform for quick n simple ajax forms


### Mailchimp Integration

1 - Add your mailchimp api-key in the ps-forms wordpress admin area (Settings --> PS Forms)
2 - Add your mailchimp list-id for the list you'd like to collect the data as a hidden input field with the following: name='ps-mailchimp-list-id' (The list-id is found in the Mailchimp admin area on the 'Lists' page, under 'Settings --> List name and defaults') 
3 - On each input that you'd like to collect data for, add a 'data-ps-mc-tag' attribute (i.e. data-ps-mc-tag="FNAME"). The value of this attribute needs to match the merge tag in mailchimp (under the 'Settings --> List fields and |MERGE| tags' menu item on your list)

