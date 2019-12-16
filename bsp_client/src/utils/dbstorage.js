import localStorageDB from "localStorageDB";
class dbstorage{
	static init(table_name,data=null){
		var lib = new localStorageDB("library", localStorage);
		if(!lib.tableExists(table_name)){
			lib.createTable(table_name, data);
			lib.commit();
		}
	}
	static checktablename(table_name){
		var lib = new localStorageDB("library", localStorage);
		return lib.tableExists(table_name)
	}
	static insert(table_name,data){
		var lib = new localStorageDB("library", localStorage);
		lib.insert(table_name,data)
		lib.commit();
	}
	static query(table_name,query){
		var lib = new localStorageDB("library", localStorage);
		if(!lib.tableExists(table_name)){
			return false
		}
		return lib.query(table_name,query)
	}
	static updateorinsert(table_name,find,to){
		var lib = new localStorageDB("library", localStorage);
		lib.insertOrUpdate(table_name, find, to);
		lib.commit();
	}
	static dropTable(table_name){
		var lib = new localStorageDB("library", localStorage);
		lib.dropTable(table_name);
		lib.commit();
	}
	static deleteRows(table_name,query){
		var lib = new localStorageDB("library", localStorage);
		lib.deleteRows(table_name,query);
		lib.commit();
	}
}

export {
    dbstorage
};
