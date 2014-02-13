ToDoList
========
This simple project is being used to develop and showcase a DomainModel abstraction class as well as dependency
injection.


The idea is to develop a system similar to that of Grails. A model class is created to reflect a database table.
This is done by extending the DomainModel class, implementing several methods, and following several guidelines. This
then allows you to perform all of the basic database interactions one might need to do. This includes:

- creating (db insert)
- delete
- get (pull)
- update (synchronize)

The idea is to unify all of these aspects into one system. So, if you're creating a database entry, you fill in
everything accept the id and call create. This will then fill in the ID with created object's id.

If you want to get data about an object based on the id, you create a new instantiation of the object and only fill in
the ID. Calling pull on it will fill in the other fields.

There are also some supporting methods as well, but those aren't as interesting, so I won't discuss them.