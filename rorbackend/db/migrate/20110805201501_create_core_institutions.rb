class CreateCoreInstitutions < ActiveRecord::Migration
  def change
    create_table :core_institutions do |t|
      t.string :name, :limit => 100
      t.integer :parent, :default => 0
      t.string :short_name, :limit => 10
      
      # t.timestamps
    end
  end
end
