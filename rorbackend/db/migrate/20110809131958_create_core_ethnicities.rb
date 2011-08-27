class CreateCoreEthnicities < ActiveRecord::Migration
  def change
    create_table :core_ethnicities do |t|
      t.string :name, :limit => 50
      
      
      # t.timestamps
    end
  end
end
